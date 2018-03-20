<?php

namespace hds\SeoBundle\Extension;

use Symfony\Component\Yaml\Yaml;

class SeoUtilsConfig
{
    const KEY_LANG = 'lang';
    const KEY_CANONICAL = 'canonical';
    const KEY_GENERAL = 'general';
    private $seoConfig;

    public function __construct()
    {
        $this->seoConfig = Yaml::parse(__DIR__ . '/../Resources/config/seo.yml');

    }

    public function getContentByBlockAndHeader($header_block, $header)
    {
        $content = '';
        $key = $header->getKey();
        if (!is_null($key)) {
            if ($key == self::KEY_LANG) {
                $configs = self::getValueByKey($this->seoConfig, self::KEY_GENERAL);
                if (!is_null($configs)) {
                    $content = $configs[self::KEY_CANONICAL];
                }

            }else{
                $configs = self::getValueByKey($this->seoConfig, $header_block->getKey());
                if (!is_null($configs)) {
                    if (array_key_exists($key, $configs)) {
                        $content = $configs[$key];
                    }

                }
            }
        }



        return $content;
    }

    private function getValueByKey($array, $key)
    {
        $result = null;
        foreach ($array as $item => $value) {
            if ($key == $item) {
                return $value;
            } else {
                if (is_array($value)) {
                    $result = self::getValueByKey($value, $key);
                }
            }
        }
        return $result;

    }


}