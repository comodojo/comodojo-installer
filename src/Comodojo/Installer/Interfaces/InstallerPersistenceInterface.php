<?php namespace Comodojo\Installer\Interfaces;

use \Comodojo\Foundation\Base\Configuration;
use \Comodojo\Installer\Components\InstallerParameters;
use \Composer\Composer;
use \Composer\IO\IOInterface;

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

interface InstallerPersistenceInterface {

    /**
     * Persistence constructor,
     * just to ensure all pieces are in the right place
     *
     * @param Composer $composer
     * @param IOInterface $io
     * @param Configuration $configuration
     * @param InstallerParameters $parameters
     */
    public function __construct(Composer $composer, IOInterface $io, Configuration $configuration, InstallerParameters $parameters);

    /**
     * Load persistent definition (if available)
     *
     * @return array
     */
    public function load();

    /**
     * Makes configuration persitent.
     *
     * @param array $data
     *
     * @return void
     */
    public function dump(array $data);

}
