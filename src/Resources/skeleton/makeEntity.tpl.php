<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use RestClient\Attribute\ApiEndpoint;
use RestClient\Attribute\HttpMethod;

#[HttpMethod(HttpMethod::<?= $method ?>)]
#[ApiEndpoint('<?= $endpoint ?>')]
class <?= $class_name ?>
{

}
