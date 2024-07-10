<?php declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Input\ArrayInput;
use function Symfony\Component\String\u;

/**
 * @author Marcin Stanik <marcin.stanik@gmail.com>
 * @since 07-2024
 * @version 1.0.0
 */
#[AsCommand(
    name: 'app:make:entity',
    description: 'Overwriting symfony make:entity',
)]
class MakeEntity extends Command
{

    /**
     * @param ManagerRegistry $Doctrine
     * @param string|null $name
     */
    public function __construct(
        private ManagerRegistry $Doctrine,
        string                  $name = null)
    {
        parent::__construct($name);
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->addOption('em', null, InputOption::VALUE_REQUIRED, 'Entity manager name, if is not set then default will be used')
            ->addArgument('name', InputArgument::REQUIRED, 'Entity name');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Symfony\Component\Console\Exception\ExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln([
            '<info>IMPORTANT</info>',
            'Every column name such like <comment>aa_bbb_ccc</comment> will be automatically changed to <comment>aaBbbCcc</comment>',
            '#######################',
            ''
        ]);

        $entityName = $input->getArgument('name');
        $entityName = u($entityName)->camel()->title()->toString(); // aaa_bb_cc => AaaBbCc
        $entityManagerName = $input->getOption('em') ?? 'default';

        /** @var EntityManager $EntityManager */
        $EntityManager = $this->Doctrine->getManager($entityManagerName);

        $tableName = u($entityName)->snake()->toString();

        // example: App\Entity\Optimax
        $entityNamespace = \array_values($EntityManager->getConfiguration()->getEntityNamespaces())[0];
        // example: Optimax
        $entityMainDirectory = \substr(\strrchr($entityNamespace, '\\'), 1);

        // example:App\Entity\Optimax\Test
        $entityClass = $entityNamespace . '\\' . $entityName;
        $entityBaseClass = $entityNamespace . '\\Base\\' . $entityName;
        $entityExists = \class_exists($entityClass);

        $Filesystem = new Filesystem();
        // example: C:/Users/m.stanik/works/programming/optimax2/src/Entity/Test.php"
        $entityFileFromPath = Path::makeAbsolute($entityName . '.php', __DIR__ . '/../Entity/');
        $entityFileToPath = Path::makeAbsolute($entityName . '.php', __DIR__ . '/../Entity/' . $entityMainDirectory . '/');

        // example: C:/Users/m.stanik/works/programming/optimax2/src/Entity/Optimax/Base/Test.php
        $entityBaseFileToPath = Path::makeAbsolute($entityName . '.php', __DIR__ . '/../Entity/' . $entityMainDirectory . '/Base/');
        // example: C:/Users/m.stanik/works/programming/optimax2/src/Repository/TestRepository.php
        $repositoryFileFromPath = Path::makeAbsolute($entityName . 'Repository.php', __DIR__ . '/../Repository/');
        // C:/Users/m.stanik/works/programming/optimax2/src/Repository/Optimax/TestRepository.php
        $repositoryFileToPath = Path::makeAbsolute($entityName . 'Repository.php', __DIR__ . '/../Repository/' . $entityMainDirectory . '/');

        // START symfony command to generate entity in main Entity directory
        $this->getApplication()->setDefaultCommand('make:entity', true);
        $command = $this->getApplication()->find('make:entity');

        $arguments = [
            'name' => $entityName,
            //'--quiet' => true,
            //'--no-interaction' => true,
            //'--regenerate'  => true,
        ];

        $greetInput = new ArrayInput($arguments);
        $returnCode = $command->run($greetInput, $output);
        // END

        if (!$entityExists && $returnCode == Command::SUCCESS) {

            if ($Filesystem->exists($entityFileFromPath)) {
                $date = (new \DateTime())->format('m-Y');

                // BASE ENTITY
                \file_put_contents($entityFileFromPath,
                    \str_replace("<?php", "<?php declare(strict_types=1);", \file_get_contents($entityFileFromPath))
                );
                \file_put_contents($entityFileFromPath,
                    \str_replace("namespace App\Entity;", "namespace App\Entity\\$entityMainDirectory\Base;", \file_get_contents($entityFileFromPath))
                );
                \file_put_contents($entityFileFromPath,
                    \str_replace("use App\Repository\\{$entityName}Repository;\n", "", \file_get_contents($entityFileFromPath))
                );
                \file_put_contents($entityFileFromPath,
                    \str_replace("#[ORM\Entity", "/**\n * @author Marcin Stanik <marcin.stanik@gmail.com>\n * @since $date\n * @version 1.0.0\n */\n#[ORM\Entity", \file_get_contents($entityFileFromPath))
                );
                \file_put_contents($entityFileFromPath,
                    \str_replace("#[ORM\Entity(repositoryClass: {$entityName}Repository::class)]", "#[ORM\MappedSuperclass()]", \file_get_contents($entityFileFromPath))
                );
                \file_put_contents($entityFileFromPath,
                    \str_replace("#[ORM\Column]\n    private ?int \$id = null;", "#[ORM\Column(options: [\"unsigned\" => true])]\n    private ?int \$id = null;", \file_get_contents($entityFileFromPath))
                );
                \file_put_contents($entityFileFromPath,
                    \str_replace("private ", "protected ", \file_get_contents($entityFileFromPath))
                );
                \file_put_contents($entityFileFromPath,
                    \str_replace("#[ORM\Id]", "\n    #[ORM\Id]", \file_get_contents($entityFileFromPath))
                );
                \file_put_contents($entityFileFromPath,
                    \str_replace("    }\n}", "    }\n\n}", \file_get_contents($entityFileFromPath))
                );

                \file_put_contents($entityFileFromPath, $this->toCamelCase(\file_get_contents($entityFileFromPath)));

                $Filesystem->copy($entityFileFromPath, $entityBaseFileToPath);

                // MAIN ENTITY
                \file_put_contents($entityFileFromPath,
                    "<?php declare(strict_types=1);

namespace App\Entity\\{$entityMainDirectory};

use App\Repository\\{$entityMainDirectory}\\{$entityName}Repository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author Marcin Stanik <marcin.stanik@gmail.com>
 * @since $date
 * @version 1.0.0
 */
#[ORM\Entity(repositoryClass: {$entityName}Repository::class)]
#[ORM\Table(name: \"{$tableName}\")]
class {$entityName} extends Base\\{$entityName}
{

}
"
                );

                $Filesystem->copy($entityFileFromPath, $entityFileToPath);
                $Filesystem->remove($entityFileFromPath);

                // ENTITY REPOSITORY
                \file_put_contents($repositoryFileFromPath,
                    \str_replace("<?php", "<?php declare(strict_types=1);", \file_get_contents($repositoryFileFromPath))
                );
                \file_put_contents($repositoryFileFromPath,
                    \str_replace("namespace App\Repository;", "namespace App\Repository\\$entityMainDirectory;", \file_get_contents($repositoryFileFromPath))
                );
                \file_put_contents($repositoryFileFromPath,
                    \str_replace("use App\Entity\\$entityName;", "use App\Entity\\$entityMainDirectory\\$entityName;", \file_get_contents($repositoryFileFromPath))
                );
                \file_put_contents($repositoryFileFromPath,
                    \str_replace(" */\nclass", " *\n * @author Marcin Stanik <marcin.stanik@gmail.com>\n * @since $date\n * @version 1.0.0\n */\nclass", \file_get_contents($repositoryFileFromPath))
                );
                \file_put_contents($repositoryFileFromPath,
                    \preg_replace("/\/{2}.*\n/", "", \file_get_contents($repositoryFileFromPath))
                );
                \file_put_contents($repositoryFileFromPath,
                    \str_replace("\n\n}", "\n}", \file_get_contents($repositoryFileFromPath))
                );
                \file_put_contents($repositoryFileFromPath,
                    \str_replace("    public function __construct(ManagerRegistry \$registry)", "    /**\n     * @param ManagerRegistry \$registry\n     */\n    public function __construct(ManagerRegistry \$registry)", \file_get_contents($repositoryFileFromPath))
                );
                \file_put_contents($repositoryFileFromPath,
                    \str_replace(
                        "    public function save($entityName \$entity",
                        "    /**\n     * @param $entityName \$$entityName\n     * @param bool \$flush\n     * @return void\n     */\n    public function save($entityName \$$entityName",
                        \file_get_contents($repositoryFileFromPath)
                    )
                );
                \file_put_contents($repositoryFileFromPath,
                    \str_replace(
                        "    public function remove($entityName \$entity",
                        "    /**\n     * @param $entityName \$$entityName\n     * @param bool \$flush\n     * @return void\n     */\n    public function remove($entityName \$$entityName",
                        \file_get_contents($repositoryFileFromPath)
                    )
                );
                \file_put_contents($repositoryFileFromPath,
                    \str_replace("\$entity", "\$$entityName", \file_get_contents($repositoryFileFromPath))
                );

                $Filesystem->copy($repositoryFileFromPath, $repositoryFileToPath);
                $Filesystem->remove($repositoryFileFromPath);
            }
        }

        return $returnCode;
    }

    /**
     * changing $aa_bbb_c to $aaBbbC and $this->aa_bbb_c to $this->aaBbbC
     * @param string $text
     * @return string
     */
    function toCamelCase(string $text): string
    {
        $matches = [];
        \preg_match_all('/\$[a-zA-Z0-9_]+/', $text, $matches);
        $matches[0] = array_unique($matches[0]);
        foreach ($matches[0] as $match) {
            if (\str_contains($match, '_') === false) {
                continue;
            }

            $match = str_replace('$', '', $match);
            $text = str_replace($match, u($match)->camel()->toString(), $text);
        }

        return $text;
    }
}

