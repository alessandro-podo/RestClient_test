<?php echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

use RestClient\Attribute\ApiEndpoint;
<?php if ($cacheIsSet) { ?>
    use RestClient\Attribute\Cache;
<?php } ?>
use RestClient\Attribute\HttpMethod;
use RestClient\Attribute\Type;
use RestClient\Attribute\Handler;
use RestClient\Attribute\Url;
use Symfony\Component\Validator\Constraints as Assert;
use <?php echo $namespace; ?>\<?php echo $successHandler; ?>;

#[HttpMethod(HttpMethod::<?php echo $method; ?>)]
#[ApiEndpoint('<?php echo $endpoint; ?>')]
<?php if ($cacheIsSet) { ?>#[Cache(cacheExpiresAfter: <?php echo $cacheExpiresAfter; ?>,cacheBeta: <?php echo $cacheBeta; ?>)]<?php } ?>
#[Url('<?php echo $url; ?>')]
#[Handler(successHandler: <?php echo $successHandler; ?>::class)]
class <?php echo $class_name; ?>

{
<?php foreach ($properties as $property) { ?>

    <?php if (is_string($property['allowedValuesString'])) { ?>
        #[Assert\Choice([<?php echo $property['allowedValuesString']; ?>])]
    <?php } ?>
    <?php if ($property['required']) { ?>
        #[Assert\NotBlank]
        #[Assert\NotNull]
    <?php } ?>
    #[Type(Type::<?php echo $property['type']; ?>)]
    private <?php echo $property['phpType']; ?> $<?php echo $property['name']; ?>;

<?php } ?>

<?php foreach ($properties as $property) { ?>
    public function get<?php echo ucfirst($property['name']); ?>():<?php echo $property['phpType']; ?>

    {
    return $this-><?php echo $property['name']; ?>;
    }

    public function set<?php echo ucfirst($property['name']); ?>(<?php echo $property['phpType']; ?> $<?php echo $property['name']; ?>):self
    {
    $this-><?php echo $property['name']; ?> = $<?php echo $property['name']; ?>;

    return $this;
    }
<?php } ?>
}
