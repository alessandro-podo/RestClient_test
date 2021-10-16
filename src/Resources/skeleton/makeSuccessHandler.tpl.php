<?= "<?php\n" ?>

declare(strict_types=1);

namespace <?= $namespace ?>;

use RestClient\DefaultHandler\SuccessHandler;

class <?= $class_name ?> extends SuccessHandler
{
public function getResult(): <?= $dtoName ?>
{
try {
return $this->serializer->denormalize($this->response->toArray(), <?= $dtoName ?>::class);
} catch (\Throwable $throwable) {

}
}
}