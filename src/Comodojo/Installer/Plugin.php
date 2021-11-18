<?php namespace Comodojo\Installer;

use \Composer\Composer;
use \Composer\IO\IOInterface;
use \Composer\Plugin\PluginInterface;
use \Composer\EventDispatcher\EventSubscriberInterface;
use \Composer\EventDispatcher\Event;
use \Comodojo\Installer\Components\InstallerConfiguration;
use \Comodojo\Foundation\Base\Configuration;
use \Exception;

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

class Plugin implements PluginInterface, EventSubscriberInterface {

    protected $installer_configuration;

    protected $comodojo_configuration;

    protected $comodojo_configuration_persistence;
    
    /**
    * {@inheritDoc}
    */
    public function deactivate(Composer $composer, IOInterface $io){
    
    }
    
    /**
    * {@inheritDoc}
    */
    public function uninstall(Composer $composer, IOInterface $io){
    
    }
    
    public function activate(Composer $composer, IOInterface $io) {

        // First, get current extra field and init a valid installer configuration
        $extra = $composer->getPackage()->getExtra();
        $parameters = isset($extra['comodojo-installer']) && is_array($extra['comodojo-installer']) ? $extra['comodojo-installer'] : [];
        $this->installer_configuration = new InstallerConfiguration($parameters);

        // Second, setup the persistence interface for the global config with "fake" configuration
        $persistence = $this->installer_configuration->getGlobalConfig()->getPersistence();
        $parameters = $this->installer_configuration->getGlobalConfig()->getParams();
        $this->comodojo_configuration_persistence = new $persistence($composer, $io, new Configuration(), $parameters);

        // Third, load current configuration, if any
        $this->comodojo_configuration = $this->loadComodojoConfiguration($io, $extra);

        // Finally, plug the installer!
        $installer = new Installer($io, $composer, $this->comodojo_configuration, $this->installer_configuration);
        $composer->getInstallationManager()->addInstaller($installer);

    }

    public static function getSubscribedEvents() {

        return ['post-create-project-cmd' => 'startPostInstallScript'];        
    
    }

    
    public function startPostInstallScript(Event $event) {

        $script = $this->installer_configuration->getPostInstallScript();
        $configuration = $this->comodojo_configuration;

        $io = $event->getIO();

        // If a script is in queue, start it now
        if ( $script !== null ) {
            $io->write("<info>Starting post-install-script</info>");
            $post_install_script = new $script($io, $configuration);
            $io->write("<info>Post-install-script ends</info>");
        }

        // Dump static global configuration
        $io->write("<info>Persisting global configuration</info>");
        $global_config = $configuration->get();
        $this->comodojo_configuration_persistence->dump($global_config);

    }

    public function loadComodojoConfiguration(IOInterface $io, array $extra) {

        // check if "real" configuration object is already in, and eventually load it
        $configuration = $this->comodojo_configuration_persistence->load();
        if ( !empty($configuration) ) {
            $io->write('<comment>Global configuration object found ad loaded</comment>');
            return new Configuration($configuration);
        }

        // if not, load it from root composer.json
        $field = $this->installer_configuration->getGlobalConfig()->getExtraField();
        if ( isset($extra[$field]) && is_array($extra[$field]) ) {
            $io->write('<comment>Global configuration retrieved from composer.json</comment>');
            $configuration = new Configuration($extra[$field]);
            // overwrite base-path with real one
            $configuration->set('base-path', getcwd());
            return $configuration;
        }

        // no global config here, init a fake one :-(
        $io->write('<comment>No global configuration, an empty one will be used</comment>');
        return new Configuration(['base-path' => getcwd()]);

    }

}
