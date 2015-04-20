<?php
class InfluxdbMetricsPlugin extends StudipPlugin implements MetricsPlugin
{
    static function onEnable($id)
    {
        RolePersistence::assignPluginRoles($id, array(7));
    }

    static function onDisable($id)
    {
        PluginManager::getInstance()->unregisterPlugin($id);
    }

    // ***** METRICSPLUGIN METHODS *****

    public static function count($stat, $value, $sampleRate = null)
    {
        self::send($stat, intval($value), $sampleRate);
    }

    public static function timing($stat, $time, $sampleRate = null)
    {
        self::send($stat, intval($time), $sampleRate);
    }

    public static function gauge($stat, $value, $sampleRate = null)
    {
        self::send($stat, intval($value), $sampleRate);
    }

    // ***** PRIVATE STUFF *****

    // Squirt the metrics over UDP
    private static function send($stat, $data, $sampleRate) {

        require_once 'InfluxdbMetricsSettings.php';
        $config = InfluxdbMetricsSettings::get();

        try {
            if (!$fp = @fsockopen('udp://' . $config['host'], $config['port'], $errno, $errstr)) {
                return;
            }
            fwrite($fp, json_encode(array(array(
                'name' => $stat,
                'columns' => array('value','timestamp'),
                'points' => array(array($data, time())),
            ))));
            fclose($fp);
        } catch (Exception $e) {
        }
    }
}
