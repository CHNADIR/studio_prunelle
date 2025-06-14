<?php

namespace App\Tests\Unit\Security\Voter;

use App\Entity\Ecole;
use App\Entity\User; // Assurez-vous que c'est votre entité User
use App\Security\Voter\EcoleVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Security;

class EcoleVoterTest extends TestCase
{
    private EcoleVoter $voter;
    private \PHPUnit\Framework\MockObject\MockObject|Security $securityMock;
    private \PHPUnit\Framework\MockObject\MockObject|TokenInterface $tokenMock;

    protected function setUp(): void
    {
        $this->securityMock = $this->createMock(Security::class);
        $this->voter = new EcoleVoter($this->securityMock);
        $this->tokenMock = $this->createMock(TokenInterface::class);
    }

    /**
     * @dataProvider provideVoteCases
     */
    public function testVote(array $userRoles, ?object $userEntity, string $attribute, $subject, int $expectedVote): void
    {
        if ($userEntity) {
            $this->tokenMock->method('getUser')->willReturn($userEntity);
        } else {
            $this->tokenMock->method('getUser')->willReturn(null); // No user / anonymous
        }

        // Simuler isGranted pour ROLE_ADMIN
        $this->securityMock
            ->method('isGranted')
            ->with('ROLE_ADMIN')
            ->willReturn(in_array('ROLE_ADMIN', $userRoles));

        $this->assertEquals($expectedVote, $this->voter->vote($this->tokenMock, $subject, [$attribute]));
    }

    public static function provideVoteCases(): iterable
    {
        $ecole = new Ecole();
        $adminUser = (new User())->setRoles(['ROLE_ADMIN']);
        $simpleUser = (new User())->setRoles(['ROLE_USER']);

        // --- Cas où `supports()` devrait retourner true ET `voteOnAttribute` décide ---
        // Admin cases
        yield 'Admin can VIEW an Ecole' => [['ROLE_ADMIN'], $adminUser, EcoleVoter::VIEW, $ecole, VoterInterface::ACCESS_GRANTED];
        yield 'Admin can EDIT an Ecole' => [['ROLE_ADMIN'], $adminUser, EcoleVoter::EDIT, $ecole, VoterInterface::ACCESS_GRANTED];
        yield 'Admin can DELETE an Ecole' => [['ROLE_ADMIN'], $adminUser, EcoleVoter::DELETE, $ecole, VoterInterface::ACCESS_GRANTED];
        yield 'Admin can CREATE (subject Ecole)' => [['ROLE_ADMIN'], $adminUser, EcoleVoter::CREATE, $ecole, VoterInterface::ACCESS_GRANTED];
        yield 'Admin can CREATE (subject null)' => [['ROLE_ADMIN'], $adminUser, EcoleVoter::CREATE, null, VoterInterface::ACCESS_GRANTED];
        yield 'Admin can CREATE (subject class)' => [['ROLE_ADMIN'], $adminUser, EcoleVoter::CREATE, Ecole::class, VoterInterface::ACCESS_GRANTED];

        // User cases (non-admin)
        yield 'User can VIEW an Ecole' => [['ROLE_USER'], $simpleUser, EcoleVoter::VIEW, $ecole, VoterInterface::ACCESS_GRANTED];
        yield 'User cannot EDIT an Ecole' => [['ROLE_USER'], $simpleUser, EcoleVoter::EDIT, $ecole, VoterInterface::ACCESS_DENIED];
        yield 'User cannot DELETE an Ecole' => [['ROLE_USER'], $simpleUser, EcoleVoter::DELETE, $ecole, VoterInterface::ACCESS_DENIED];
        yield 'User cannot CREATE (subject Ecole)' => [['ROLE_USER'], $simpleUser, EcoleVoter::CREATE, $ecole, VoterInterface::ACCESS_DENIED];
        yield 'User cannot CREATE (subject null)' => [['ROLE_USER'], $simpleUser, EcoleVoter::CREATE, null, VoterInterface::ACCESS_DENIED];

        // No user (anonymous)
        // Pour VIEW, EDIT, DELETE, CREATE sur une école, si l'utilisateur n'est pas authentifié, voteOnAttribute renverra false.
        yield 'Anonymous cannot VIEW Ecole' => [[], null, EcoleVoter::VIEW, $ecole, VoterInterface::ACCESS_DENIED];
        yield 'Anonymous cannot CREATE Ecole' => [[], null, EcoleVoter::CREATE, null, VoterInterface::ACCESS_DENIED];


        // --- Cas où `supports()` devrait retourner false, donc `vote()` devrait ABSTAIN ---
        yield 'Unsupported attribute results in ABSTAIN' => [['ROLE_USER'], $simpleUser, 'UNSUPPORTED_ATTR', $ecole, VoterInterface::ACCESS_ABSTAIN];
        yield 'VIEW with non-Ecole subject results in ABSTAIN' => [['ROLE_USER'], $simpleUser, EcoleVoter::VIEW, new \stdClass(), VoterInterface::ACCESS_ABSTAIN];
        // Pour EDIT et DELETE, si le sujet est null, supports() retourne false.
        yield 'EDIT with null subject results in ABSTAIN' => [['ROLE_USER'], $simpleUser, EcoleVoter::EDIT, null, VoterInterface::ACCESS_ABSTAIN];
        yield 'DELETE with null subject results in ABSTAIN' => [['ROLE_USER'], $simpleUser, EcoleVoter::DELETE, null, VoterInterface::ACCESS_ABSTAIN];
        // Pour CREATE, supports() retourne true même si le sujet est non-Ecole (comme stdClass),
        // mais voteOnAttribute devrait alors refuser l'accès si l'utilisateur n'est pas admin.
        yield 'User cannot CREATE with non-Ecole subject (stdClass)' => [['ROLE_USER'], $simpleUser, EcoleVoter::CREATE, new \stdClass(), VoterInterface::ACCESS_DENIED];
        yield 'Admin can CREATE with non-Ecole subject (stdClass)' => [['ROLE_ADMIN'], $adminUser, EcoleVoter::CREATE, new \stdClass(), VoterInterface::ACCESS_GRANTED];

    }
}