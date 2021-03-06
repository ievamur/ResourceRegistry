<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/**
 * ResourceRegistry3
 * 
 * @package     RR3
 * @author      Middleware Team HEAnet 
 * @copyright   Copyright (c) 2012, HEAnet Limited (http://www.heanet.ie)
 * @license     MIT http://www.opensource.org/licenses/mit-license.php
 *  
 */

/**
 * RR3 Helpers
 *
 * @package     RR3
 * @subpackage  Helpers
 * @author      Janusz Ulanowski <janusz.ulanowski@heanet.ie>
 */


function validateX509($cert, $args = NULL)
{
    if (empty($cert))
    {
        return FALSE;
    } else
	{
		$cert = getPEM($cert);
        $cert_result = openssl_x509_parse($cert);
        if (empty($cert_result) OR !is_array($cert_result))
        {
            return FALSE;
        }
        if (!empty($args) && is_array($args) && count($args) > 0)
        {
            if (array_key_exists('validity', $args) && ($args['validity'] == TRUE))
            {
                /**
                 * TODO
                 */
            }
        } else
        {
            return TRUE;
        }
    }
}

function reformatPEM($value)
{
    $cleaned_value = $value;
    $cleaned_value = str_replace('-----BEGIN CERTIFICATE-----', '', $cleaned_value);
    $cleaned_value = str_replace('-----END CERTIFICATE-----', '', $cleaned_value);
    $cleaned_value = preg_replace("/\r\n/","", $cleaned_value);
    $cleaned_value = preg_replace("/\n+/","", $cleaned_value);
    $cleaned_value = preg_replace('/\s\s+/', "", $cleaned_value);
    $cleaned_value = preg_replace('/\s*/', "", $cleaned_value);
    $cleaned_value= trim($cleaned_value);
    $pem = chunk_split($cleaned_value, 64, "\n");
    return $pem;
}

// Get PEM formated certificate from quickform input
// if raw is true, then ommit the begin/end certificate delimiter
function getPEM($value, $raw = false)
{

    $cleaned_value = preg_replace('#(\\\r)#', '', $value);
    $cleaned_value = preg_replace('#(\\\n)#', "\n", $value);

    $cleaned_value = trim($cleaned_value);

    // Add or remove BEGIN/END lines
    if ($raw)
    {
        $cleaned_value = preg_replace('-----BEGIN CERTIFICATE-----', '', $cleaned_value);
        $cleaned_value = preg_replace('-----END CERTIFICATE-----', '', $cleaned_value);
        $cleaned_value = trim($cleaned_value);
    } else
    {
        if (!empty($cleaned_value) && !preg_match('/-----BEGIN CERTIFICATE-----/', $cleaned_value))
        {
            $cleaned_value = "-----BEGIN CERTIFICATE-----\n" . $cleaned_value;
        }
        if (!empty($cleaned_value) && !preg_match('/-----END CERTIFICATE-----/', $cleaned_value))
        {
            $cleaned_value .= "\n-----END CERTIFICATE-----";
        }
    }

    return $cleaned_value;
}

function PEMtoHTML($value)
{
    $cleaned_value = nl2br($value);
    return $cleaned_value;
}
