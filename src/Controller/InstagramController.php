<?php

/**
 * @file
 * Contains \Drupal\acton\Controller\ActonMailController.
 */

namespace Drupal\instagram\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\instagram\InstagramApi;
use Drupal\instagram\InstagramStorage;


class InstagramController extends ControllerBase {


    protected $configFactory;
    protected $instagramApi;
    protected $storage;


    /**
     * InstagramController constructor.
     * @param InstagramApi $instagramApi
     */
    public function __construct(InstagramApi $instagramApi, InstagramStorage $instagramStorage) {
        $this->instagramApi = $instagramApi;
        $this->storage = $instagramStorage;
    }


    /**
     * @param ContainerInterface $container
     * @return static
     */
    public static function create(ContainerInterface $container) {
        return new static(
            $container->get('instagram.instagram_api'),
            $container->get('instagram.instagram_storage')
        );
    }


    /**
     * @param bool $truncate
     * @return array|bool
     */
    public function getImages($truncate = false){

        // Get config
        $config = $this->config('instagram.settings');

        if($config->get('hashtag') && $config->get('hashtag') != ''){

            return $this->storeTaggedImages($truncate);

        }

        if($config->get('user_id') && $config->get('user_id') != ''){

            return $this->storeUserImages($truncate);

        }

        return false;

    }



    /**
     * @return array
     */
    private function storeTaggedImages($truncate = false){

        // Get config
        $config = $this->config('instagram.settings');

        // Set keys for api calls
        $this->instagramApi->setConfig($config->get('api_key'));
        $this->instagramApi->setAccessToken($config->get('access_token'));

        // Get images using api
        $images = $this->instagramApi->getTagMedia($config->get('hashtag'),$config->get('count'));

        // Save images to DB
        if($this->storage->storeImages($images,$truncate)){

            $response = $config->get('count')." Instagram images have been saved to the database";
            drupal_set_message($response);

        }else{

            $response = "An error occured while saving instagram images to the database";
            drupal_set_message($response, 'error');

        }

        $build[] = array(
            '#type' => 'markup',
            '#markup' => $response,
        );
        $build['#cache']['max-age'] = 0;

        return $build;

    }


    /**
     * @return array
     */
    private function storeUserImages($truncate = false){

        // Get config
        $config = $this->config('instagram.settings');

        // Set keys for api calls
        $this->instagramApi->setConfig($config->get('api_key'));
        $this->instagramApi->setAccessToken($config->get('access_token'));

        // Get images using api
        $images = $this->instagramApi->getUserMedia($config->get('user_id'),$config->get('count'));

        // Save images to DB
        if($this->storage->storeImages($images,$truncate)){

            $response = $config->get('count')." Instagram images have been saved to the database";
            drupal_set_message($response);

        }else{

            $response = "An error occured while saving instagram images to the database";
            drupal_set_message($response, 'error');

        }

        $build[] = array(
            '#type' => 'markup',
            '#markup' => $response,
        );
        $build['#cache']['max-age'] = 0;

        return $build;

    }


}