<?php

/**
 * @file
 * Contains \Drupal\instagram\Form\instagramSettingsForm.
 */

namespace Drupal\instagram\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\instagram\Controller\InstagramController;


/**
 * Instagram form.
 */
class InstagramSettingsForm extends ConfigFormBase {


    /**
     * @var InstagramController
     */
    protected $controller;


    /**
     * InstagramSettingsForm constructor.
     * @param InstagramController $controller
     */
    public function __construct(InstagramController $controller) {
        $this->controller = $controller;
    }


    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container) {
        return new static(
            $container->get('instagram.instagram_controller')
        );
    }


    /**
     * {@inheritdoc}
     */
    protected function getEditableConfigNames() {
        return [
            'instagram.settings',
        ];
    }


    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'instagram_settings_form';
    }


    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {

        $config = $this->config('instagram.settings');

        $form['intro'] = array(
            '#type' => 'html_tag',
            '#tag' => 'p',
            '#value' => $this->t('You must enter either an Instagram user id or a hashtag for your feed.'),
            );

        $form['api_key'] = array(
            '#type' => 'textfield',
            '#title' => t('API Key'),
            '#required' => TRUE,
            '#default_value' => $config->get('api_key')
        );

        $form['api_secret'] = array(
            '#type' => 'textfield',
            '#title' => t('API Secret'),
            '#default_value' => $config->get('api_secret')
        );

        $form['api_callback'] = array(
            '#type' => 'textfield',
            '#title' => t('API Callback'),
            '#default_value' => $config->get('api_callback')
        );

        $form['access_token'] = array(
            '#type' => 'textfield',
            '#title' => t('Access Token'),
            '#required' => TRUE,
            '#default_value' => $config->get('access_token')
        );

        $form['user_id'] = array(
            '#type' => 'textfield',
            '#title' => t('User ID'),
            '#default_value' => $config->get('user_id'),
            '#description' => t('Enter to pull the latest images restricted to a specific account')
        );

        $form['hashtag'] = array(
            '#type' => 'textfield',
            '#title' => t('Hashtag'),
            '#default_value' => $config->get('hashtag'),
            '#description' => t('Enter to pull all public images tagged with a specific hashtag')
        );

        $form['count'] = array(
            '#type' => 'number',
            '#title' => t('Number of images to pull (max 20)'),
            '#required' => TRUE,
            '#default_value' => $config->get('count')
        );

        return parent::buildForm($form, $form_state);
    }


    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state) {

        // Get form values
        $form_values = $form_state->getValues();

        // Check required fields
        if ($form_values['api_key'] == '') {
            $form_state->setErrorByName('api_key', $this->t('The API key field is required'));
        }
        if ($form_values['access_token'] == '') {
            $form_state->setErrorByName('access_token', $this->t('The access token field is required'));
        }
        if ($form_values['user_id'] != '' && $form_values['hashtag'] != '') {
            $form_state->setErrorByName('both', $this->t('Both the user id and hashtag fields contain a value. Please enter only one or the other'));
        }
        if ($form_values['user_id'] == '' && $form_values['hashtag'] == '') {
            $form_state->setErrorByName('none', $this->t('A user id OR a hashtag is required'));
        }
        if ($form_values['count'] == '') {
            $form_state->setErrorByName('count', $this->t('The count field is required'));
        }

    }


    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {

        // Get form values
        $form_values = $form_state->getValues();

        // Set config from from values
        $this->config('instagram.settings')
            ->set('api_key', $form_values['api_key'])
            ->set('api_secret', $form_values['api_secret'])
            ->set('api_callback', $form_values['api_callback'])
            ->set('access_token', $form_values['access_token'])
            ->set('user_id', $form_values['user_id'])
            ->set('hashtag', $form_values['hashtag'])
            ->set('count', $form_values['count'])
            ->save();

        // Truncate table and store images to DB
        $this->controller->getImages(true);

        parent::submitForm($form, $form_state);

    }

}