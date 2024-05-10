<?php

namespace Drupal\weather_app\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides a 'Weather Tunis' block.
 *
 * @Block(
 *   id = "weather_tunis_block",
 *   admin_label = @Translation("Weather Tunis Block"),
 * )
 */
class WeatherAppBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $api_key = '175eda3d094f87ca6e6a53cc9b836278';
    $city = 'Tunis';

    try {
      $client = \Drupal::httpClient();
      $response = $client->get("https://api.openweathermap.org/data/2.5/weather?q=$city&appid=$api_key&units=metric");
      $data = json_decode($response->getBody());

      $temp = isset($data->main->temp) ? $data->main->temp : '';
      $description = isset($data->weather[0]->main) ? $data->weather[0]->main : '';

      $image_url = $this->getWeatherImageUrl($description);

      return [
        '#theme' => 'weather_tunis_block',
        '#temperature' => $temp,
        '#description' => $description,
        '#weather_image' => $image_url,
        '#attached' => [
          'library' => ['weather_app/weather_styles'],
        ],
      ];
    } catch (\Exception $e) {
      return [
        '#markup' => $this->t('Could not retrieve weather data.'),
      ];
    }
  }

  /**
   * Returns the appropriate image URL based on weather conditions.
   */
  private function getWeatherImageUrl($weather_condition) {
    $weather_condition = strtolower($weather_condition);
    switch ($weather_condition) {
      case 'clear':
        return '/modules/custom/weather_app/images/sunny.jpg';
      case 'clouds':
        return '/modules/custom/weather_app/images/cloudy.jpg';
      case 'rain':
        return '/modules/custom/weather_app/images/rainy.webp';
      default:
        return '/modules/custom/weather_app/images/snowy.png';
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'access content');
  }
}
