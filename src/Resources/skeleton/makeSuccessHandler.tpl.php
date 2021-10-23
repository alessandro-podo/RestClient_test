<?php echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

use RestClient\Interfaces\RestClientResponseInterface;
use RestClient\DefaultHandler\SuccessHandler;

class <?php echo $class_name; ?> extends SuccessHandler
{
    /**
    * @return <?php echo $dtoName; ?>|RestClientResponseInterface
    */
    public function getResult(): RestClientResponseInterface
    {
        try {
            return $this->serializer->denormalize($this->response->toArray(), <?php echo $dtoName; ?>::class)->setStatusCode($this->response->getStatusCode());
        } catch (\Throwable $throwable) {

        }
    }
}