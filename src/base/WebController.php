<?php

namespace selvinortiz\raft\base;

use Craft;
use craft\helpers\ArrayHelper;
use craft\helpers\Json;
use craft\web\Response;

class WebController extends \craft\web\Controller
{
    private $_params;
    private $_rawBody;

    public function init()
    {
        $this->_params = array_merge(
            Craft::$app->request->getQueryParams(),
            Craft::$app->request->getBodyParams(),
        );

        $this->_rawBody = Craft::$app->getRequest()->getRawBody();

        if (!empty($this->_rawBody))
        {
            try
            {
                $decoded = Json::decode($this->_rawBody);
                $this->_params = array_merge(
                    $this->_params,
                    is_array($decoded) ? $decoded : [],
                );
            } catch (\Exception $e)
            {
                // Ignore exception
            }
        }
    }

    public function param($key, $default = null)
    {
        return ArrayHelper::getValue($this->_params, $key, $default);
    }

    public function asJsonWithSuccess($message, $data = [])
    {
        $data['success']  = $data['success'] ?? true;
        $data['message']  = $data['message'] ?? $message;
        $data['received'] = $data['received'] ?? $this->_params;

        if (!empty($data['message']))
        {
            $data['message'] = Craft::t('site', mb_strtolower($data['message']));
        }

        $response         = Craft::$app->getResponse();
        $response->format = Response::FORMAT_JSON;
        $response->data   = $data;

        return $response;
    }

    public function asJsonWithError($message, $data = [])
    {
        $data['success']  = $data['success'] ?? false;
        $data['message']  = $data['message'] ?? $message;
        $data['received'] = $data['received'] ?? $this->_params;

        if (!empty($data['message']))
        {
            $data['message'] = Craft::t('site', mb_strtolower($data['message']));
        }

        $response         = Craft::$app->getResponse();
        $response->format = Response::FORMAT_JSON;
        $response->data   = $data;

        return $response;
    }

    /**
     * @param string $message
     * @param array  $data
     *
     * @return null
     */
    public function asResponseWithSuccess($message, array $data = [])
    {
        $data['success']  = $data['success'] ?? true;
        $data['message']  = $data['message'] ?? $message;
        $data['received'] = $data['received'] ?? $this->_params;

        if (!empty($data['message']))
        {
            $data['message'] = Craft::t('site', mb_strtolower($data['message']));
        }

        Craft::$app->getUrlManager()->setRouteParams($data);

        return;
    }

    /**
     * Helper method for controllers that want to reload a template with variables
     *
     * @param string $message
     * @param array  $data
     *
     * @return null
     */
    public function asResponseWithError(string $message, array $data = [])
    {
        $data['success']  = $data['success'] ?? false;
        $data['message']  = $data['message'] ?? $message;
        $data['received'] = $data['received'] ?? $this->_params;

        if (!empty($data['message']))
        {
            $data['message'] = Craft::t('site', mb_strtolower($data['message']));
        }

        Craft::$app->getUrlManager()->setRouteParams($data);

        return;
    }
}
