<?php namespace Comodojo\Installer;

use \Composer\Composer;
use \Composer\IO\IOInterface;
use \Composer\Installer\LibraryInstaller;
use \Composer\Package\PackageInterface;
use \Composer\Repository\InstalledRepositoryInterface;
use \Comodojo\Installer\Components\InstallerConfiguration;
use \Comodojo\Foundation\Base\Configuration;
use \Comodojo\Exception\InstallerException;
use \Comodojo\Installer\Components\InstallerDriverManager;
use \Comodojo\Foundation\Utils\ArrayOps;
use React\Promise\PromiseInterface;

/**
 * @package     Comodojo Framework
 * @author      Marco Giovinazzi <marco.giovinazzi@comodojo.org>
 * @license     MIT
 *
 * LICENSE:
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

class Installer extends LibraryInstaller {

    protected $supported_drivers;

    protected $supported_packages;

    protected $drivers = [];
    
    public function __construct(
        IOInterface $io,
        Composer $composer,
        Configuration $configuration,
        InstallerConfiguration $installer_configuration
    ) {
        
        $this->supported_packages = $installer_configuration->getPackageTypes();

        $extra = $installer_configuration->getPackageExtra();
        $this->supported_drivers = array_keys($extra);

        foreach ($extra as $name => $specs) {
            $this->drivers[$name] = new InstallerDriverManager($composer, $io, $configuration, $specs);
        }

        parent::__construct($io, $composer);

    }

    /**
     * {@inheritDoc}
     */
    public function supports($packageType) {

        return in_array($packageType, $this->supported_packages);

    }
    
    /**
     * {@inheritDoc}
     */
    public function install(InstalledRepositoryInterface $repo, PackageInterface $package) {
        
        $installPackage = function() use ($package) {

            $this->packageInstall($package);
        
        };
        
        // Composer v2 return a promise
        $promise = parent::install($repo, $package);

        if ($promise instanceof PromiseInterface) {
            
            return $promise->then($installPackage);

        }

        $installPackage();

        return null;

    }

    /**
     * {@inheritDoc}
     */
    public function update(InstalledRepositoryInterface $repo, PackageInterface $initial, PackageInterface $target) {

        $updatePackage = function() use ($initial, $target) {

            $this->packageUpdate($initial, $target);
        
        };
        
        // Composer v2 return a promise
        $promise = parent::update($repo, $initial, $target);

        if ($promise instanceof PromiseInterface) {
            
            return $promise->then($updatePackage);

        }

        $updatePackage();

        return null;
        
    }

    /**
     * {@inheritDoc}
     */
    public function uninstall(InstalledRepositoryInterface $repo, PackageInterface $package) {

        $uninstallPackage = function() use ($package) {

            $this->packageUninstall($package);
        
        };
        
        // Composer v2 return a promise
        $promise = parent::uninstall($repo, $package);

        if ($promise instanceof PromiseInterface) {
            
            return $promise->then($uninstallPackage);

        }

        $uninstallPackage();

        return null;        
        
    }

    private function packageInstall($package) {

        // get package properties
        $package_name = $package->getPrettyName();
        $package_path = $this->composer->getInstallationManager()->getInstallPath($package);
        $package_version = $package->getPrettyVersion();
        $package_extra = $package->getExtra();

        // parse package content
        $supported_fields = ArrayOps::filterByKeys($this->supported_drivers, $package_extra);

        // invoke driver
        foreach ($supported_fields as $name => $config) {

            $repo = $this->drivers[$name];
            $driver = $repo->getDriver();
            $driver->install($package_name, $package_path, $config);

        }

    }

    private function packageUninstall($package) {

        // get package properties
        $package_name = $package->getPrettyName();
        $package_path = $this->composer->getInstallationManager()->getInstallPath($package);
        $package_version = $package->getPrettyVersion();
        $package_extra = $package->getExtra();

        // parse package content
        $supported_fields = ArrayOps::filterByKeys($this->supported_drivers, $package_extra);

        // invoke driver
        foreach ($supported_fields as $name => $config) {

            $repo = $this->drivers[$name];
            $driver = $repo->getDriver();
            $driver->uninstall($package_name, $package_path, $config);

        }

    }

    private function packageUpdate($initial, $target) {

        // get initial package properties
        $initial_package_name = $initial->getPrettyName();
        $initial_package_path = $this->composer->getInstallationManager()->getInstallPath($initial);
        $initial_package_version = $initial->getPrettyVersion();
        $initial_package_extra = $initial->getExtra();

        // get target package properties
        $target_package_name = $target->getPrettyName();
        $target_package_path = $this->composer->getInstallationManager()->getInstallPath($target);
        $target_package_version = $target->getPrettyVersion();
        $target_package_extra = $target->getExtra();

        // parse package content
        $initial_supported_fields = ArrayOps::filterByKeys($this->supported_drivers, $initial_package_extra);
        $target_supported_fields = ArrayOps::filterByKeys($this->supported_drivers, $target_package_extra);

        list($uninstall, $update, $install) = ArrayOps::circularDiffKeys($initial_supported_fields, $target_supported_fields);

        foreach ($uninstall as $name => $config) {

            $repo = $this->drivers[$name];
            $driver = $repo->getDriver();
            $driver->uninstall($initial_package_name, $initial_package_path, $config);

        }

        foreach ($install as $name => $config) {

            $repo = $this->drivers[$name];
            $driver = $repo->getDriver();
            $driver->install($target_package_name, $target_package_path, $config);

        }

        foreach ($update as $name => $config) {

            $repo = $this->drivers[$name];
            $driver = $repo->getDriver();
            $driver->update($target_package_name, $target_package_path, $initial_supported_fields[$name], $target_supported_fields[$name]);

        }

    }

}
