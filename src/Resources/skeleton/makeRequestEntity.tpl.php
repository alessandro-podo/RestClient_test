<?= "<?php\n" ?>

declare(strict_types=1);

namespace <?= $namespace ?>;

use RestClient\Attribute\ApiEndpoint;
<?php if ($cacheIsSet): ?>
    use RestClient\Attribute\Cache;
<?php endif; ?>
use RestClient\Attribute\HttpMethod;
use RestClient\Attribute\Type;
use RestClient\Attribute\Handler;
use RestClient\Attribute\Url;
use Symfony\Component\Validator\Constraints as Assert;

#[HttpMethod(HttpMethod::<?= $method ?>)]
#[ApiEndpoint('<?= $endpoint ?>')]
<?php if ($cacheIsSet): ?>#[Cache(cacheExpiresAfter: <?= $cacheExpiresAfter ?>,cacheBeta: <?= $cacheBeta ?>)]<?php endif; ?>
#[Url('<?= $url ?>')]
#[Handler(successHandler: <?= $successHandler ?>::class)]
class <?= $class_name ?>

{
<?php foreach ($properties as $property): ?>

    <?php if (is_string($property["allowedValuesString"])): ?>
        #[Assert\Choice([<?= $property["allowedValuesString"] ?>])]
    <?php endif; ?>
    <?php if ($property["required"]): ?>
        #[Assert\NotBlank]
        #[Assert\NotNull]
    <?php endif; ?>
    #[Type(Type::<?= $property["type"] ?>)]
    private <?= $property["phpType"] ?> $<?= $property["name"] ?>;

<?php endforeach; ?>

<?php foreach ($properties as $property): ?>
    public function get<?= ucfirst($property["name"]) ?>():<?= $property["phpType"] ?>

    {
    return $this-><?= $property["name"] ?>;
    }

    public function set<?= ucfirst($property["name"]) ?>(<?= $property["phpType"] ?> $<?= $property["name"] ?>):self
    {
    $this-><?= $property["name"] ?> = $<?= $property["name"] ?>;

    return $this;
    }
<?php endforeach; ?>
}
