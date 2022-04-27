<?php

/**
 * GenerateProductQRBlock File Doc Comment.
 *
 * PHP version  7.4.3
 *
 * @description File used to generate QR Code
 * @category Block_Page_For_QR_Code_Generation
 * @package GenerateProductQRBlock
 * @author Author <paurnimas@cybage.com>
 * @license https://opensource.org/licenses/MIT MIT License
 * @link Drupal\jugaad_patches\Plugin\Block
 */

namespace Drupal\jugaad_patches\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Endroid\QrCode\QrCode;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Endroid\QrCode\Encoding\Encoding;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Label\Label;
use Psr\Log\LoggerInterface;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;

define('UPLOAD_DIR', 'sites/default/files/assets/');

/**
 * Template Class Doc Comment
 * Provides a block with a QR code generation.
 *
 * @category GenerateProductQRBlock
 * @package QR_Code_Generation_Block_Php
 * @author paurnima Savkare <paurnimas@cybage.com>
 * @license https://opensource.org/licenses/MIT MIT License
 * @link Drupal\jugaad_patches\Plugin\Block
 * @Block(
 *   id = "qr_code_generation",
 *   admin_label = @Translation("QR Code Generation"),
 * )
 */
class GenerateProductQRBlock extends BlockBase implements
    ContainerFactoryPluginInterface {
/**
 * RouteMatch used to get parameter Node.
 *
 * @var \Drupal\Core\Routing\RouteMatchInterface
 * @package GenerateProductQRBlock
 * @category QR Code generation Block php
 * @license https://opensource.org/licenses/MIT MIT License
 * @link Drupal\jugaad_patches\Plugin\Block
 */
protected $routeMatch;

/**
 * Constructs a new Block Object.
 *
 * @param array $configuration
 *   Information about the plugin instance.
 * @param string $plugin_id
 *   The plugin_id for the plugin instance.
 * @param string $plugin_definition
 *   The plugin implementation definition.
 * @param string $route_match
 *   Route match variable.
 * @param string $logger
 *   Logger variable.
 */
public function __construct(array $configuration,
  $plugin_id,
  array $plugin_definition,
  RouteMatchInterface $route_match,
  LoggerInterface $logger
    ) {
  parent::__construct($configuration, $plugin_id, $plugin_definition);
  $this->routeMatch = $route_match;

}

/**
 * {@inheritdoc}
 *
 * @param string $container
 *   ID for plugin.
 * @param array $configuration
 *   Array of configuration.
 * @param string $plugin_id
 *   Container variable.
 * @param string $plugin_definition
 *   Defination of plugin.
 *
 * @return static
 *   static values
 */
public static function create(ContainerInterface $container,
  array $configuration,
  $plugin_id,
  $plugin_definition) {
  return new static(
            $configuration,
            $plugin_id,
            $plugin_definition,
            $container->get('current_route_match'),
            $container->get('logger.factory')->get('jugaad_patches'),
        );
}

/**
 * {@inheritdoc}
 *
 * @return string
 *   QR Code
 */
public function build() {
  $node = $this->routeMatch->getParameter('node');
  // Create a basic QR code.
  if ($node instanceof \Drupal\node\NodeInterface) {
    // Condition for Products content type.
    if ($node->bundle() == 'products') {
      $nid = $node->id();
      $qrUrl = $node->get('field_app_purchase_link')->getValue()[0]['uri'];
      if (!empty($qrUrl)) {
        try {
          $qrCode = new QrCode($qrUrl);
          $qrCode->setErrorCorrectionLevel(
                            new ErrorCorrectionLevelLow()
                        )
            ->setSize(300)
            ->setMargin(10)
            ->setRoundBlockSizeMode(new RoundBlockSizeModeMargin())
            ->setEncoding(new Encoding('UTF-8'))
            ->setForegroundColor(new Color(0, 0, 0))
            ->setBackgroundColor(new Color(255, 255, 255));
          $writer = new PngWriter();
          // Create generic label.
          $label = Label::create('Scan here to buy')
             ->setTextColor(new Color(255, 0, 0));
          $result = $writer->write($qrCode, NULL, $label);
          header("Content-Type: " . $result->getMimeType());
          $dataUri = $result->getDataUri();
          $img = str_replace('data:image/png;base64,', '', $dataUri);
          $img = str_replace(' ', '+', $img);
          $data = base64_decode($img);
          $path = "sites/default/files/assets/";
          if (!is_dir($path)) {
            mkdir($path, 0777, TRUE);
          }
          $file = UPLOAD_DIR . $nid . '.png';
          $success = file_put_contents($file, $data);
          catch (Exception $e) {
            // Log the exception to watchdog.
            $err = Error::decodeException($e);
            $this->logger->error(
                            '%type: @message in %function 
                        (line %line of %file).', $ex_vars
            );
          }
        }
      }
    }
    return [
      '#markup' => '<img src="' . $GLOBALS['base_url'] . '/' . $path .
      basename($file) . '" >',
    ];
  }

  /**
   * {@inheritdoc}
   *
   * @param string $account
   *   This variable holds account value.
   *
   * @return AccessResult
   *   If User can access content
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'access content');
  }

  /**
   * {@inheritdoc}
   *
   * @return int
   *   0 If you want to disable caching for this block.
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
