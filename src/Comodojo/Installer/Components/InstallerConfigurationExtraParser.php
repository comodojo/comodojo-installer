<?php namespace Comodojo\Installer\Components;

use \Comodojo\Foundation\Utils\ArrayOps;
use \Comodojo\Exception\InstallerException;

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

class InstallerConfigurationExtraParser {

    protected $properties;

    protected $parameters;

    // "package-extra": {
    //     "routes": {
    //         "driver": "\\Comodojo\\Installer\\Drivers\\RouteDriver",
    //         "persistence": "\\Comodojo\\Installer\\Persistence\\YamlPersistence",
    //         "params": {
    //             "config-file": "config/comodojo-routes.yml"
    //         }
    //     },
    //     "plugins": {
    //         "driver": "\\My\\Installer\\Drivers\\PluginDriver",
    //         "driver-source": "src/My/Installer/Drivers/PluginDriver.php",
    //         "persistence": "\\Comodojo\\Installer\\Persistence\\DatabasePersistence",
    //         "persistence-source": "src/My/Installer/Persistence/DatabasePersistence.php",
    //         "params": {
    //             "config-file": "config/comodojo-plugins.yml"
    //         }
    //     }
    // },

    public function __construct($name, array $content) {

        if ( empty($content['driver']) ) throw new InstallerException("Missing driver for field $name");
        if ( empty($content['persistence']) ) throw new InstallerException("Missing persistence for field $name");

        $this->properties = [
            "driver" => $content['driver'],
            "driver-source" => null,
            "persistence" => $content['persistence'],
            "persistence-source" => null
        ];

        if ( !empty($content['driver-source']) ) $this->properties['driver-source'] = $content['driver-source'];
        if ( !empty($content['persistence-source']) ) $this->properties['persistence-source'] = $content['persistence-source'];

        $parameters = isset($content['params']) && is_array($content['params']) ? $content['params'] : [] ;

        $this->parameters = new InstallerParameters($parameters);

    }

    public function getDriver() {

        return $this->properties['driver'];

    }

    public function getDriverSource() {

        return $this->properties['driver-source'];

    }

    public function getPersistence() {

        return $this->properties['persistence'];

    }

    public function getPersistenceSource() {

        return $this->properties['persistence-source'];

    }

    public function getParams() {

        return $this->parameters;

    }

}
