<?php
/**
 * @file
 * Contains \Drupal\campaignmonitor\Controller\CampaignMonitorController.
 */

namespace Drupal\campaignmonitor\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactory;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\campaignmonitor\CampaignMonitor;

class CampaignMonitorController extends ControllerBase {

  /**
   * The campaign monitor.
   *
   * @var \Drupal\campaignmonitor\CampaignMonitor
   */
  protected $campaignMonitor;

  /**
   * Settings for the module.
   *
   * @var array
   */
  protected $settings;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * The form builder service.
   *
   * @var \Drupal\Core\Form\FormBuilder
   */
  protected $formBuilder;

  /**
   * Constructs a new SubscribeForm.
   */
  public function __construct(ConfigFactory $configFactory, FormBuilder $formBuilder) {
    $this->campaignMonitor = CampaignMonitor::GetConnector();
    $this->configFactory = $configFactory;
    $this->formBuilder = $formBuilder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('form_builder')
    );
  }

  /**
   * Constructs a page with a signup form.
   */
  public function content() {
    $settings = $this->configFactory->get('campaignmonitor.general');
    $page = $settings->get('page');

    // If the page option isn't turned on, throw an access denied error.
    if ($page != TRUE) {
      throw new AccessDeniedHttpException();
    }

    $cm = $this->campaignMonitor;
    $lists = $cm->getLists();

    //additions AA
    $newsletters_config = $this->config('campaignmonitor.lists');
    $lang_code = \Drupal::languageManager()->getCurrentLanguage()->getId();
    //additionsend

    $enabled_lists = array();
    foreach ($lists as $list_id => $enabled) {
      //additions AA
      $restore_key_fr = $list_id . '.enabled_fr';
      $restore_key_en = $list_id . '.enabled_en';
      $enabled_fr = $newsletters_config->get($restore_key_fr);
      $enabled_en = $newsletters_config->get($restore_key_en);
      if ($lang_code=='fr' && !$enabled_fr)
        continue;
      if ($lang_code=='en' && !$enabled_en)
        continue;
      //additionsEnd
       $enabled_lists[$list_id] = $lists[$list_id]['name'];
     }

    // Prefix text.
    $prefix = $settings->get('page_prefix');
    $prefix = $prefix['value'];

    $form = $this->formBuilder->getForm('Drupal\campaignmonitor\Form\SubscribeForm',
      array('enabled_lists' => $enabled_lists));

    return array(
      'prefix' => array('#markup' => $prefix),
      'subscribe_form' => $form,
    );
  }

}
