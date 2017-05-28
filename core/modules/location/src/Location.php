<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

namespace Pyshnov\location;


use Pyshnov\Core\Config\ConfigInterface;
use Pyshnov\Core\Cookies\Cookies;
use Pyshnov\Core\DB\DB;
use Symfony\Component\HttpFoundation\Request;

class Location
{
    protected $config;

    protected $city;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    public function init(Request $request)
    {
        //$request->getSession()->set('cid', 1);
        if ($request->attributes->has('location')) {

            $stmt = DB::select('*', DB_PREFIX . '_city')
                ->where('aliases', '=', $request->attributes->get('location'));

        } elseif (Cookies::get('cid', $request->getSession()->get('cid', false))) {

            $stmt = DB::select('*', DB_PREFIX . '_city')
                ->where('city_id', '=', Cookies::get('cid', $request->getSession()->get('cid')));

        } else {

            $city_id = $this->geoIP($request->getClientIp()) ?: $this->config->get('city_default');

            $stmt = DB::select('*', DB_PREFIX . '_city')
                ->where('city_id', '=', $city_id);
        }

        $row = $stmt->limit(1)->execute()->fetchObject();

        if ($row) {
            $this->city = new City($row->city_id, $row->name, $row->aliases, $row->region_id, unserialize($row->declension));
            if(!Cookies::has('cid') || Cookies::get('cid') != $row->city_id) {
                $cookie = new Cookies('cid', $row->city_id, '+1 year');
                $cookie->send();
                $request->getSession()->set('cid', $row->city_id);
            }
        }

    }

    /**
     * @param $ip
     * @return bool|int
     */
    protected function geoIP($ip)
    {
        $url = 'http://ipgeobase.ru:7020/geo?ip=' . $ip;

        $result = @simplexml_load_file($url);

        if (isset($result->ip->city)) {
            $stmt = DB::select('city_id', DB_PREFIX . '_city')
                ->multiWhere([
                    'name' => trim($result->ip->city),
                    'active' => 1
                ])
                ->limit(1)
                ->execute();

            if ($res = $stmt->fetch()) {
                return $res['city_id'];
            }
        }

        return false;
    }

    /**
     * @return City
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param City $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }


}