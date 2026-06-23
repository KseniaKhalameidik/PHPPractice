<?php

namespace App\Tests\Integration\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Override;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\ApplicationTester;
use Symfony\Component\HttpKernel\KernelInterface;
use App\Command\UserAddRoleCommand;
use Symfony\Component\Console\Tester\CommandTester; 

class UserAddRoleCommandTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private ApplicationTester $applicationTester;

    #[Override]
    public function setUp(): void
    {
        parent::setUp();
        self::bootKernel();        

        $this->em = static::getContainer()->get(EntityManagerInterface::class);

        $testUser = (new User())
            ->setEmail('test@test.com')
            ->setPassword('123')
        ;

        $this->em->persist($testUser);
        $this->em->flush();

        $application = new Application(static::$kernel);
        $application->setAutoExit(false);

        $this->applicationTester = new ApplicationTester($application);
    }

    #[Override]
    public function tearDown(): void
    {
        parent::tearDown();

        $schemaTool = new SchemaTool($this->em);
        $metaData = $this->em->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metaData);
        $schemaTool->createSchema($metaData);
    }

    public function testUserAddRoleSuccess(): void
    {
        $testUserEmail = 'test@test.com';

        $this->applicationTester->run([
            'command' => 'app:user:add-role',
            'email' => $testUserEmail,
        ]);

        $this->applicationTester->assertCommandIsSuccessful();

        $testUser = $this->em->getRepository(User::class)->findOneBy(['email' => $testUserEmail]);
        $this->assertContains('ROLE_ADMIN', $testUser->getRoles());
    }


    public function testUserAddRoleFaileduserNotFound(): void
    {
        //проблема с перехватом потоков вывода (STDOUT/STDERR) в applicationTester
        $command = static::getContainer()->get(UserAddRoleCommand::class);
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'email' => 'invalid@invalid.com',
        ]);

        $this->assertEquals(
            Command::INVALID,
            $commandTester->getStatusCode()
        );

        //Получаем вывод (CommandTester корректно захватывает и STDOUT, и STDERR)
        $output = $commandTester->getDisplay();

        $this->assertStringContainsString(
            "User with this email not found: invalid@invalid.com",
            $output
        );
    }
}