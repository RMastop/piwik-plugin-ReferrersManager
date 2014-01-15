<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package ReferrersManager
 */
namespace Piwik\Plugins\ReferrersManager;

use Piwik\Common;
use Piwik\DataTable\Renderer\Json;
use Piwik\Piwik;
use Piwik\Plugin\ControllerAdmin;
use Piwik\UrlHelper;
use Piwik\View;

/**
 *
 * @package ReferrersManager
 */
class Controller extends ControllerAdmin
{
    /**
     * Index action
     * @return string
     */
    public function index()
    {
        Piwik::checkUserIsSuperUser();

        $view = new View('@ReferrersManager/index');
        $this->setBasicVariablesView($view);

        $view->searchEngineInfos = $this->getSearchEngineInfos();
        $view->searchEngineLogos = $this->getSearchEngineLogos();

        $view->socialInfos = $this->getSocialsInfos();
        $view->socialLogos = $this->getSocialsLogos();

        return $view->render();
    }

    /**
     * Ajax action to check an url for search engine information that can be extracted
     *
     * @return string
     */
    public function checkUrl()
    {
        $this->checkTokenInUrl();

        $urlToCheck = Common::unsanitizeInputValue(Common::getRequestVar('url', null, 'string'));

        $detectedEngine = UrlHelper::extractSearchEngineInformationFromUrl($urlToCheck);

        if (!empty($detectedEngine['name'])) {
            $detectedEngine['image'] = \Piwik\Plugins\Referrers\getSearchEngineLogoFromUrl(\Piwik\Plugins\Referrers\getSearchEngineUrlFromName($detectedEngine['name']));
        }

        Json::sendHeaderJSON();
        return json_encode($detectedEngine);
    }

    /**
     * Returns all search engine informations known to piwik
     *
     * @return array
     */
    protected function getSearchEngineInfos()
    {

        $mergedSearchInfos = array();

        $searchEngineInfos = Common::getSearchEngineUrls();

        foreach ($searchEngineInfos AS $url => $infos) {

            $infos = array_merge($infos, array('', '', '', ''));

            list($name, $parameters, $backlink, $charset) = $infos;

            if (is_array($parameters)) {
                $parameters = implode(', ', $parameters);
            }

            if (empty($mergedSearchInfos[$name])) {
                $mergedSearchInfos[$name] = array();
            }

            $mergedSearchInfos[$name][] = array(
                'url'        => $url,
                'parameters' => $parameters,
                'backlink'   => $backlink,
                'charset'    => $charset
            );

        }

        ksort($mergedSearchInfos, SORT_NATURAL|SORT_FLAG_CASE);

        return $mergedSearchInfos;
    }

    /**
     * Returns an array containing all logos for search engines
     *
     * @return array (name => logo-src)
     */
    protected function getSearchEngineLogos()
    {
        $searchEngineLogos = array();

        $searchEngineNames = Common::getSearchEngineNames();
        foreach($searchEngineNames AS $name => $url) {
            $searchEngineLogos[$name] = \Piwik\Plugins\Referrers\getSearchEngineLogoFromUrl($url);
        }
        return $searchEngineLogos;
    }

    /**
     * Returns all social informations known to piwik
     *
     * @return array
     */
    protected function getSocialsInfos()
    {
        require PIWIK_INCLUDE_PATH . '/core/DataFiles/Socials.php';

        $socials = $GLOBALS['Piwik_socialUrl'];

        $mergedSocials = array();

        foreach ($socials AS $url => $name) {

            $mergedSocials[$name][] = $url;
        }

        ksort($mergedSocials, SORT_NATURAL|SORT_FLAG_CASE);

        return $mergedSocials;
    }

    /**
     * Returns an array containing all logos for socials
     *
     * @return array (name => logo-src)
     */
    protected function getSocialsLogos()
    {
        require PIWIK_INCLUDE_PATH . '/core/DataFiles/Socials.php';

        $socialsLogos = array();

        $socials = $GLOBALS['Piwik_socialUrl'];
        foreach($socials AS $url => $name) {
            $socialsLogos[$name] = \Piwik\Plugins\Referrers\getSocialsLogoFromUrl($url);
        }
        return $socialsLogos;
    }

}
