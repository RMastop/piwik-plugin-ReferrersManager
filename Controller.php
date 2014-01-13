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
    public function checkUrl()
    {
        $this->checkTokenInUrl();

        $urlToCheck = Common::getRequestVar('url', null, 'string');

        $detectedEngine = UrlHelper::extractSearchEngineInformationFromUrl($urlToCheck);

        if (!empty($detectedEngine['name'])) {
            $detectedEngine['image'] = \Piwik\Plugins\Referrers\getSearchEngineLogoFromUrl(\Piwik\Plugins\Referrers\getSearchEngineUrlFromName($detectedEngine['name']));
        }

        Json::sendHeaderJSON();
        return Common::json_encode($detectedEngine);

    }


    public function index()
    {
        Piwik::checkUserIsSuperUser();

        $view = new View('@ReferrersManager/index');
        $this->setBasicVariablesView($view);
        $view->searchEngineInfos = $this->getSearchEngineInfos();
        $view->searchEngineLogos = $this->getSearchEngineLogos();
        return $view->render();
    }

    protected function getSearchEngineInfos()
    {

        $mergedSearchInfos = array();

        $searchEngineInfos = Common::getSearchEngineUrls();

        foreach ($searchEngineInfos AS $url => $infos) {

            $name = $infos[0];
            $parameters = @$infos[1];
            $backlink = @$infos[2];
            $charset = @$infos[3];

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

    protected function getSearchEngineLogos()
    {
        $searchEngineLogos = array();

        $searchEngineNames = Common::getSearchEngineNames();
        foreach($searchEngineNames AS $name => $url) {
            $searchEngineLogos[$name] = \Piwik\Plugins\Referrers\getSearchEngineLogoFromUrl($url);
        }
        return $searchEngineLogos;
    }

}
