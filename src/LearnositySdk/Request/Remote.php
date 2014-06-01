<?php
namespace LearnositySdk\Request;

/**
 *--------------------------------------------------------------------------
 * Learnosity SDK - Remote
 *--------------------------------------------------------------------------
 *
 * Used to execute a request to a public endpoint. Useful as a cross
 * domain proxy.
 *
 */

class Remote
{
    private $result = null;

    /**
     * Execute a resource request (GET) to an endpoint. Useful as a
     * cross-domain proxy.
     *
     * @param  string $url      Full URL of where to POST the request
     * @param  array  $request  Payload of request
     * @param  bool   $options  Optional Curl options
     *
     * @return string           The response string
     */
    public function get($url, $data = array(), $options = array())
    {
        $query = http_build_query($data);
        if (!empty($query)) {
            $url = (strpos($url, '?')) ? $url . '&' . $query : $url . '?' . $query;
        }

        $this->request($url, false, $options);

        return $this;
    }

    /**
     * Execute a resource request (POST) to an endpoint. Useful as a
     * cross-domain proxy.
     *
     * @param  string $url      Full URL of where to POST the request
     * @param  array  $request  Payload of request
     * @param  bool   $options  Optional Curl options
     *
     * @return string           The response string
     */
    public function post($url, $data = array(), $options = array())
    {
        $this->request($url, $data, $options);

        return $this;
    }

    private function request($url, $post = false, $options = array())
    {
        $defaults = array(
            'timeout'  => 10,
            'headers'  => array(),
            'encoding' => 'utf-8'
        );

        $options = array_merge($defaults, $options);
        $ch = curl_init();

        $params = array(
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_ENCODING       => $options['encoding'],
            CURLOPT_AUTOREFERER    => true,
            CURLOPT_CONNECTTIMEOUT => $options['timeout'],
            CURLOPT_TIMEOUT        => $options['timeout'],
            CURLOPT_MAXREDIRS      => 10
        );

        if (!empty($options['headers'])) {
            $params[CURLOPT_HTTPHEADER] = $options['headers'];
        }

        if (!empty($post)) {
            $params[CURLOPT_POST] = true;
            $params[CURLOPT_POSTFIELDS] = $post;
        }

        curl_setopt_array($ch, $params);

        $body          = curl_exec($ch);
        $error_code    = curl_errno($ch);
        $error_message = curl_error($ch);
        $response      = curl_getinfo($ch);

        curl_close($ch);

        $response['error_code']    = $error_code;
        $response['error_message'] = $error_message;
        $response['body']          = $body;

        $this->result = $response;
    }

    public function getBody()
    {
        return $this->result['body'];
    }

    public function getError()
    {
        return array(
            'code'    => $this->result['error_code'],
            'message' => $this->result['error_message']
        );
    }

    public function getHeader($type = 'content_type')
    {
        return (array_key_exists($type, $this->result)) ? $this->result[$type] : null;
    }

    public function getSize()
    {
        return $this->result['size_download'];
    }

    public function getStatusCode()
    {
        return $this->result['http_code'];
    }
}
