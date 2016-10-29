<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\ReferrersManager\Activity;

use Piwik\Piwik;
use Piwik\Plugins\ActivityLog\Activity\Activity;

class SearchEngineAdded extends Activity
{
    protected $eventName = 'API.ReferrersManager.addSearchEngine.end';

    /**
     * Returns data to be used for logging the event
     *
     * @param array $eventData Array of data passed to postEvent method
     * @return array
     */
    public function extractParams($eventData)
    {
        list($success, $finalAPIParameters) = $eventData;

        // $finalAPIParameters = [ className, module, action, parameters ]
        // $finalAPIParameters[parameters] = [ 0=>name, 1=>host, 2=>parameters, 3=>backlink, 4=>charset ]

        return [
            'items' => [
                [
                    'type' => 'searchengine',
                    'data' => [
                        'name' => $finalAPIParameters['parameters'][0],
                        'host' => $finalAPIParameters['parameters'][1],
                    ]
                ]
            ],
        ];
    }

    /**
     * Returns the translated description of the logged event
     *
     * @param array $activityData
     * @param string $performingUser
     * @return string
     */
    public function getTranslatedDescription($activityData, $performingUser)
    {
        return Piwik::translate('ReferrersManager_SearchEngineAdded');
    }
}