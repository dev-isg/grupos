<?php
namespace Application\View\Helper;
use \Zend\View\Helper;
use \Zend\Form\View\Helper\AbstractHelper;
/**
 * Url Canonica
 *
 * @author jcarbajal
 *        
 */
class CanonicalUrl extends AbstractHelper
{

    /**
     * Llamado de la función canonicaUrl
     *
     * @param string $url
     *            Url a convertir en canonical
     * @return string Url Canonica
     */
    public function __invoke($url, $options = null)
    {
        $pattern = '/[^a-zA-Z0-9]/u';
        $str = preg_replace('/\s\s+/', ' ', preg_replace($pattern, ' ', $url));
        $replace = array(
            "á",
            "à",
            "é",
            "è",
            "í",
            "ì",
            "ó",
            "ò",
            "ú",
            "ù",
            "ñ",
            "Ñ",
            "Á",
            "À",
            "É",
            "È",
            "Í",
            "Ì",
            "Ó",
            "Ò",
            "Ú",
            "Ù"
        );
        $change = array(
            "a",
            "a",
            "e",
            "e",
            "i",
            "i",
            "o",
            "o",
            "u",
            "u",
            "n",
            "N",
            "A",
            "A",
            "E",
            "E",
            "I",
            "I",
            "O",
            "O",
            "U",
            "U"
        );
        if (!isset($options['spaceChar'])) $options['spaceChar'] = '-';
        $str = str_replace($replace, $change, $str);
        $canonical = str_replace(" ", $options['spaceChar'], strtolower($str));
        if (isset($options['prefix']))
            $canonical = $options['prefix'] . $options['spaceChar'] . $canonical;
        if (isset($options['suffix']))
            $canonical = $canonical . $options['spaceChar'] . $options['suffix'];

        return $canonical;
    }
}