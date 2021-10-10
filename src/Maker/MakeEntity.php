<?php

namespace RestClient\Maker;


use RestClient\Attribute\HttpMethod;
use RestClient\Attribute\Type;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 * @author Ryan Weaver <weaverryan@gmail.com>
 */
final class MakeEntity extends AbstractMaker
{

    private array $possibleConnections;
    private array $possibleMethods;
    private array $possibleTypes;
    private array $fields = [];

    public function __construct(private ParameterBagInterface $parameterBag)
    {
        $this->possibleConnections = array_keys($this->parameterBag->get("rest_client.connections"));
        $this->possibleMethods = (new \ReflectionClass(HttpMethod::class))->getConstants();
        $this->possibleTypes = (new \ReflectionClass(Type::class))->getConstants();
    }

    public static function getCommandName(): string
    {
        return 'make:restClient:entity';
    }

    public static function getCommandDescription(): string
    {
        return 'Create a new Entity for a Rest Request';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConf)
    {
        $command
            ->addArgument('endpointUrl', InputArgument::REQUIRED, 'Wie ist die relative URL des Endpoints?')
            ->addArgument('apiEndpoint', InputArgument::OPTIONAL, 'Welchen Endpoint Betrifft es?')
            ->addArgument('apiMethod', InputArgument::OPTIONAL, 'Welche HTTP Methode soll verwendent werden?')
            ->setHelp('Hilfe');

        $inputConf->setArgumentAsNonInteractive('apiEndpoint');
        $inputConf->setArgumentAsNonInteractive('apiMethod');
    }

    public function interact(InputInterface $input, ConsoleStyle $io, Command $command)
    {
        if (null === $input->getArgument('apiMethod')) {
            $argument = $command->getDefinition()->getArgument('apiMethod');


            $question = new Question($argument->getDescription());
            $question->setValidator(function ($answer) {
                if (!in_array($answer, $this->possibleMethods)) {
                    throw new \RuntimeException(
                        'You must use one of these Methods ' . implode(',', $this->possibleMethods)
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
                if (!in_array($answer, $this->possibleConnections)) {
                    throw new \RuntimeException(
                        'You must use one of these Connections ' . implode(',', $this->possibleConnections) . ' or create a new Connection in the ConfigFile'
                    );
                }

                return $answer;
            });
            $question->setAutocompleterValues($this->possibleConnections);
            $question->setMaxAttempts(3);

            $input->setArgument('apiEndpoint', $io->askQuestion($question));
        }

        while (true) {
            $newProperty = $this->askForNewProperty($io);

            if (null === $newProperty) {
                break;
            }
            $this->fields[$newProperty['name']] = $newProperty;
        }
    }


    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator)
    {
        $endpointUrl = $input->getArgument('endpointUrl');
        $apiEndpoint = $input->getArgument('apiEndpoint');
        $apiMethod = strtoupper($input->getArgument('apiMethod'));
        if (!in_array($apiMethod, $this->possibleMethods)) {
            $io->error('You must use one of these Methods ' . implode(',', $this->possibleMethods));
            return Command::FAILURE;
        }
        if (!in_array($apiEndpoint, $this->possibleConnections)) {
            $io->error('You must use one of these Connections ' . implode(',', $this->possibleConnections));
            return Command::FAILURE;
        }

        try {
            $this->splitUrl($endpointUrl);
        } catch (\Throwable $throwable) {
            $io->error($throwable->getMessage());
            return Command::FAILURE;
        }

        $splitedUrlParts = $this->splitUrl($endpointUrl);

        $formClassNameDetails = $generator->createClassNameDetails(
            ucfirst(strtolower($apiMethod)) . implode("", $splitedUrlParts['forClassName']),
            'RestEndpoint\\' . implode("\\", $splitedUrlParts['forClassName']) . '\\' . ucfirst(strtolower($apiMethod))
        );

        $generator->generateClass(
            $formClassNameDetails->getFullName(),
            __DIR__ . '/../Resources/skeleton/makeEntity.tpl.php',
            [
                'endpoint' => $apiEndpoint,
                'method' => $apiMethod,
                'properties' => $this->fields
            ]
        );


        $generator->writeChanges();
        $this->writeSuccessMessage($io);

        return Command::SUCCESS;
    }

    private function splitUrl(string $url): array
    {
        $splitUrlParts = explode('/', $url);
        $splitUrl = [];

        foreach ($splitUrlParts as $splitUrlPart) {
            if (strlen($splitUrlPart) > 0) {
                $splitUrl["likeInput"][] = $splitUrlPart;
                $splitUrl["forClassName"][] = ucfirst(strtolower($splitUrlPart));
            }
        }

        if (count($splitUrl) === 0) {
            throw new \RuntimeException(sprintf('Url can not be splited %s ', $url));
        }

        return $splitUrl;
    }

    private function askForNewProperty(ConsoleStyle $io): ?array
    {
        $io->writeln('');

        $question = (new Question('Wie soll das Feld genannt werden? (leerlassen zum beenden)'))
            ->setValidator(function ($answer) {
                if (array_key_exists($answer, $this->fields)) {
                    throw new \RuntimeException('This Property is already set');
                }
                return $answer;
            });
        $feldName = $io->askQuestion($question);

        if ($feldName === null) {
            return null;
        }

        $phpTypes = ['string', 'int', 'float', 'array'];
        $question = (new Question('Welchen PhpTyp soll das Feld ' . $feldName . ' haben?'))
            ->setValidator(function ($answer) use ($phpTypes) {
                if (!in_array($answer, $phpTypes)) {
                    throw new \RuntimeException(
                        'You must use one of these Types ' . implode(',', $phpTypes)
                    );
                }

                return $answer;
            })
            ->setAutocompleterValues($phpTypes)
            ->setMaxAttempts(3);
        $phpTyp = $io->askQuestion($question);

        $question = (new Question('Welchen RequestTyp soll das Feld ' . $feldName . ' haben?'))
            ->setValidator(function ($answer) {
                if (!in_array($answer, $this->possibleTypes)) {
                    throw new \RuntimeException(
                        'You must use one of these Types ' . implode(',', $this->possibleTypes)
                    );
                }

                return $answer;
            })
            ->setAutocompleterValues($this->possibleTypes)
            ->setMaxAttempts(3);
        $type = $io->askQuestion($question);

        $question = (new ConfirmationQuestion('Is ' . $feldName . ' required?', false));
        $required = $io->askQuestion($question);

        $allowedValuesString = null;
        if ($phpTyp === 'array') {
            $question = (new Question('Welche Werte sollen zulässig sein. (Auswahl kommasepertiert,leer für alles)'))
                ->setValidator(function ($answer) {
                    if (strlen($answer) === 0) {
                        return null;
                    }
                    return explode(",", $answer);
                })
                ->setMaxAttempts(3);
            $allowedValues = $io->askQuestion($question);

            $allowedValuesString = "";
            foreach ($allowedValues as $allowedValue) {
                $allowedValuesString .= "'" . trim($allowedValue) . "',";
            }
            $allowedValuesString = substr($allowedValuesString, 0, -1);
        }

        return [
            'type' => $type,
            'name' => $feldName,
            'phpType' => $phpTyp,
            'required' => $required,
            'allowedValuesString' => $allowedValuesString
        ];
    }


    public function configureDependencies(DependencyBuilder $dependencies)
    {

    }
}
