<?php echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

use RestClient\Interfaces\RestClientResponseInterface;

class <?php echo $class_name; ?> extends RestClientResponseInterface
{
public function getResult(): <?php echo $dtoName; ?>
{
try {
return $this->serializer->denormalize($this->response->toArray(), <?php echo $dtoName; ?>::class)->setStatusCode($this->response->getStatusCode());
} catch (\Throwable $throwable) {

}
}
}