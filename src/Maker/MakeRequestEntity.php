<?php

declare(strict_types=1);

namespace RestClient\Maker;

use RestClient\Attribute\HttpMethod;
use RestClient\Attribute\Type;
use RuntimeException;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

final class MakeRequestEntity extends AbstractMaker
{
    private array $possibleConnections;
    private array $possibleMethods;
    private array $possibleTypes;
    private array $fields = [];

    public function __construct(private ParameterBagInterface $parameterBag)
    {
        $this->possibleConnections = array_keys($this->parameterBag->get('rest_client.connections'));
        $this->possibleMethods = (new \ReflectionClass(HttpMethod::class))->getConstants();
        $this->possibleTypes = (new \ReflectionClass(Type::class))->getConstants();
    }

    public static function getCommandName(): string
    {
        return 'make:restClient:requestEntity';
    }

    public static function getCommandDescription(): string
    {
        return 'Create a new Entity for a Rest Request';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConf): void
    {
        $command
            ->addArgument('endpointUrl', InputArgument::REQUIRED, 'Wie ist die relative URL des Endpoints?')
            ->addArgument('apiEndpoint', InputArgument::OPTIONAL, 'Welchen Endpoint Betrifft es?')
            ->addArgument('apiMethod', InputArgument::OPTIONAL, 'Welche HTTP Methode soll verwendent werden?')
            ->addOption('cacheExpiresAfter', null, InputOption::VALUE_OPTIONAL, 'Sekunden wie lange der Cache gültig sein soll für den Request', $this->parameterBag->get('rest_client.cache')['expiresAfter'])
            ->addOption('cacheBeta', null, InputOption::VALUE_OPTIONAL, 'recompute for the Cache', $this->parameterBag->get('rest_client.cache')['beta'])
            ->setHelp('Create a RequestEntity. If one Cache Parameter is set, the Attribute will be written and use default parameter from the Config')
        ;

        $inputConf->setArgumentAsNonInteractive('apiEndpoint');
        $inputConf->setArgumentAsNonInteractive('apiMethod');
    }

    public function interact(InputInterface $input, ConsoleStyle $io, Command $command): void
    {
        if (null === $input->getArgument('apiMethod')) {
            $argument = $command->getDefinition()->getArgument('apiMethod');

            $question = new Question($argument->getDescription());
            $question->setValidator(function ($answer) {
                if (!\in_array($answer, $this->possibleMethods, true)) {
                    throw new \RuntimeException(
                        'You must use one of these Methods '.implode(',', $this->possibleMethods)
                    );
                }

                return $answer;
            });
            $question->setAutocompleterValues($this->possibleMethods);
            $question->setMaxAttempts(3);

            $input->setArgument('apiMethod', $io->askQuestion($question));
        }

        if (null === $input->getArgument('apiEndpoint')) {
            $argument = $command->getDefinition()->getArgument('apiEndpoint');

            $question = new Question($argument->getDescription());
            $question->setValidator(function ($answer) {
                if (!\in_array($answer, $this->possibleConnections, true)) {
                    throw new \RuntimeException(
                        'You must use one of these Connections '.implode(',', $this->possibleConnections).' or create a new Connection in the ConfigFile'
                    );
                }

                return $answer;
            });
            $question->setAutocompleterValues($this->possibleConnections);
            $question->setMaxAttempts(3);

            $input->setArgument('apiEndpoint', $io->askQuestion($question));
        }

        if (!is_numeric($input->getOption('cacheBeta'))) {
            $argument = $command->getDefinition()->getOption('cacheBeta');

            $io->error('Ihr übergebener Parameter, war kein Numerischer Wert');
            $question = new Question($argument->getDescription());
            $question->setValidator(function ($answer) {
                if (!is_numeric($answer)) {
                    throw new \RuntimeException(
                        'Die Eingabe muss numerisch sein'
                    );
                }

                return $answer;
            });
            $question->setMaxAttempts(3);

            $input->setOption('cacheBeta', $io->askQuestion($question));
        }

        if (!is_numeric($input->getOption('cacheExpiresAfter'))) {
            $argument = $command->getDefinition()->getOption('cacheExpiresAfter');

            $io->error('Ihr übergebener Parameter, war kein Numerischer Wert');
            $question = new Question($argument->getDescription());
            $question->setValidator(function ($answer) {
                if (!is_numeric($answer)) {
                    throw new \RuntimeException(
                        'Die Eingabe muss numerisch sein'
                    );
                }

                return $answer;
            });
            $question->setMaxAttempts(3);

            $input->setOption('cacheExpiresAfter', $io->askQuestion($question));
        }

        while (true) {
            $newProperty = $this->askForNewProperty($io);

            if (null === $newProperty) {
                break;
            }
            $this->fields[$newProperty['name']] = $newProperty;
        }
    }

    /**
     * @throws \Exception
     */
    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator)
    {
        $endpointUrl = $input->getArgument('endpointUrl');
        $apiEndpoint = $input->getArgument('apiEndpoint');
        $apiMethod = mb_strtoupper($input->getArgument('apiMethod'));
        $cacheBeta = $input->getOption('cacheBeta');
        $cacheExpiresAfter = $input->getOption('cacheExpiresAfter');

        $cacheIsSet = (!\is_bool($input->getParameterOption('--cacheBeta')) || !\is_bool($input->getParameterOption('--cacheExpiresAfter')));

        if (!\in_array($apiMethod, $this->possibleMethods, true)) {
            $io->error('You must use one of these Methods '.implode(',', $this->possibleMethods));

            return Command::FAILURE;
        }
        if (!\in_array($apiEndpoint, $this->possibleConnections, true)) {
            $io->error('You must use one of these Connections '.implode(',', $this->possibleConnections));

            return Command::FAILURE;
        }

        try {
            $this->splitUrl($endpointUrl, $apiEndpoint);
        } catch (\Throwable $throwable) {
            $io->error($throwable->getMessage());

            return Command::FAILURE;
        }

        $splitedUrlParts = $this->splitUrl($endpointUrl, $apiEndpoint);

        $uc_apiEndpoint = ucfirst(mb_strtolower($apiEndpoint));
        $uc_apiMethod = ucfirst(mb_strtolower($apiMethod));
        $classNameParts = $splitedUrlParts['forClassName'];

        $formClassNameDetailsEntity = $generator->createClassNameDetails(
            $uc_apiEndpoint . $uc_apiMethod .implode('', $classNameParts),
            $this->parameterBag->get('rest_client.namespacePräfix').'\\'. $uc_apiEndpoint .'\\'.implode('\\', $classNameParts),
            'Request'
        );
        $formClassNameDetailsDto = $generator->createClassNameDetails(
            $uc_apiEndpoint . $uc_apiMethod .implode('', $classNameParts),
            $this->parameterBag->get('rest_client.namespacePräfix').'\\'. $uc_apiEndpoint .'\\'.implode('\\', $classNameParts),
            'Dto'
        );
        $formClassNameDetailsSuccessHandler = $generator->createClassNameDetails(
            $uc_apiEndpoint . $uc_apiMethod .implode('', $classNameParts),
            $this->parameterBag->get('rest_client.namespacePräfix').'\\'. $uc_apiEndpoint .'\\'.implode('\\', $classNameParts),
            'SuccessHandler'
        );

        $generator->generateClass(
            $formClassNameDetailsEntity->getFullName(),
            __DIR__.'/../Resources/skeleton/makeRequestEntity.tpl.php',
            [
                'endpoint' => $apiEndpoint,
                'method' => $apiMethod,
                'cacheBeta' => $cacheBeta,
                'cacheExpiresAfter' => $cacheExpiresAfter,
                'cacheIsSet' => $cacheIsSet,
                'properties' => $this->fields,
                'url' => $endpointUrl,
                'successHandler' => $formClassNameDetailsSuccessHandler->getRelativeName(),
            ]
        );

        //SuccessHandler
        $generator->generateClass(
            $formClassNameDetailsSuccessHandler->getFullName(),
            __DIR__.'/../Resources/skeleton/makeSuccessHandler.tpl.php',
            [
                'dtoName' => $formClassNameDetailsDto->getRelativeName(),
            ]
        );

        //dto
        $generator->generateClass(
            $formClassNameDetailsDto->getFullName(),
            __DIR__.'/../Resources/skeleton/makeDto.tpl.php',
            []
        );

        $generator->writeChanges();
        $this->writeSuccessMessage($io);

        return Command::SUCCESS;
    }

    public function configureDependencies(DependencyBuilder $dependencies): void
    {
    }

    /**
     * @throws RuntimeException
     */
    private function splitUrl(string $url, string $apiEndpoint): array
    {
        $praefix = "";
        if(isset($this->parameterBag->get('rest_client.connections')[$apiEndpoint]["urlPräfix"])){
            $praefix = $this->parameterBag->get('rest_client.connections')[$apiEndpoint]["urlPräfix"];
        }

        $url = str_replace($praefix, "", $url);
        $splitUrlParts = explode('/', $url);
        $splitUrl = [];

        foreach ($splitUrlParts as $splitUrlPart) {
            if (\mb_strlen($splitUrlPart) > 0) {
                $splitUrl['likeInput'][] = $splitUrlPart;
                $splitUrlPart = str_replace("{","",$splitUrlPart);
                $splitUrlPart = str_replace("}","",$splitUrlPart);
                $splitUrl['forClassName'][] = ucfirst(mb_strtolower($splitUrlPart));
            }
        }

        if (0 === \count($splitUrl)) {
            throw new RuntimeException(sprintf('Url can not be splited %s ', $url));
        }

        return $splitUrl;
    }

    private function askForNewProperty(ConsoleStyle $io): ?array
    {
        $io->writeln('');

        $question = (new Question('Wie soll das Feld genannt werden? (leerlassen zum beenden)'))
            ->setValidator(function ($answer) {
                if (\array_key_exists($answer, $this->fields)) {
                    throw new \RuntimeException('This Property is already set');
                }

                return $answer;
            })
        ;
        $feldName = $io->askQuestion($question);

        if (null === $feldName) {
            return null;
        }

        $phpTypes = ['string', 'int', 'float', 'array'];
        $question = (new Question('Welchen PhpTyp soll das Feld '.$feldName.' haben?'))
            ->setValidator(function ($answer) use ($phpTypes) {
                if (!\in_array($answer, $phpTypes, true)) {
                    throw new \RuntimeException(
                        'You must use one of these Types '.implode(',', $phpTypes)
                    );
                }

                return $answer;
            })
            ->setAutocompleterValues($phpTypes)
            ->setMaxAttempts(3)
        ;
        $phpTyp = $io->askQuestion($question);

        $question = (new Question('Welchen RequestTyp soll das Feld '.$feldName.' haben?'))
            ->setValidator(function ($answer) {
                if (!\in_array($answer, $this->possibleTypes, true)) {
                    throw new \RuntimeException(
                        'You must use one of these Types '.implode(',', $this->possibleTypes)
                    );
                }

                return $answer;
            })
            ->setAutocompleterValues($this->possibleTypes)
            ->setMaxAttempts(3)
        ;
        $type = $io->askQuestion($question);

        $question = (new ConfirmationQuestion('Is '.$feldName.' required?', false));
        $required = $io->askQuestion($question);

        $allowedValuesString = null;
        if ('array' === $phpTyp) {
            $question = (new Question('Welche Werte sollen zulässig sein. (Auswahl kommasepertiert,leer für alles)'))
                ->setValidator(function ($answer) {
                    if (0 === \mb_strlen($answer)) {
                        return null;
                    }

                    return explode(',', $answer);
                })
                ->setMaxAttempts(3)
            ;
            $allowedValues = $io->askQuestion($question);

            $allowedValuesString = '';
            foreach ($allowedValues as $allowedValue) {
                $allowedValuesString .= "'".trim($allowedValue)."',";
            }
            $allowedValuesString = mb_substr($allowedValuesString, 0, -1);
        }

        return [
            'type' => $type,
            'name' => $feldName,
            'phpType' => $phpTyp,
            'required' => $required,
            'allowedValuesString' => $allowedValuesString,
        ];
    }
}
