<?php

namespace App\Tests\Unit\Security\Voter;

use App\Entity\PriseDeVue;
use App\Entity\User; // Assurez-vous que c'est votre entité User
use App\Security\Voter\PriseDeVueVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Security;

class PriseDeVueVoterTest extends TestCase
{
    private PriseDeVueVoter $voter;
    private \PHPUnit\Framework\MockObject\MockObject|Security $securityMock;
    private \PHPUnit\Framework\MockObject\MockObject|TokenInterface $tokenMock;

    protected function setUp(): void
    {
        $this->securityMock = $this->createMock(Security::class);
        $this->voter = new PriseDeVueVoter($this->securityMock);
        $this->tokenMock = $this->createMock(TokenInterface::class);
    }

    /**
     * @dataProvider provideVoteCases
     */
    public function testVote(array $userRoles, ?User $userEntity, string $userEmail, string $photographeEmail, string $attribute, $subject, int $expectedVote): void
    {
        // Le sujet est passé directement au lieu d'être créé ici pour les cas d'abstention
        // $priseDeVue = (new PriseDeVue())->setPhotographe($photographeEmail);

        if ($userEntity) {
            // Assurez-vous que User a setEmail ou adaptez la création/mock de l'utilisateur
            if (method_exists($userEntity, 'setEmail')) {
                $userEntity->setEmail($userEmail);
            }
            $this->tokenMock->method('getUser')->willReturn($userEntity);
        } else {
            $this->tokenMock->method('getUser')->willReturn(null);
        }

        $this->securityMock
            ->method('isGranted')
            ->with('ROLE_ADMIN')
            ->willReturn(in_array('ROLE_ADMIN', $userRoles));

        $this->assertEquals($expectedVote, $this->voter->vote($this->tokenMock, $subject, [$attribute]));
    }

    public static function provideVoteCases(): iterable
    {
        $adminUser = (new User())->setRoles(['ROLE_ADMIN']);
        $photographerUser = (new User())->setRoles(['ROLE_USER']);
        $otherUser = (new User())->setRoles(['ROLE_USER']);

        $photographeEmail = 'photographer@example.com';
        $otherEmail = 'other@example.com';

        $validPriseDeVue = (new PriseDeVue())->setPhotographe($photographeEmail);

        // --- Cas où `supports()` devrait retourner true ET `voteOnAttribute` décide ---
        // Admin cases
        yield 'Admin can VIEW' => [['ROLE_ADMIN'], $adminUser, 'admin@example.com', $photographeEmail, PriseDeVueVoter::VIEW, $validPriseDeVue, VoterInterface::ACCESS_GRANTED];
        yield 'Admin can EDIT' => [['ROLE_ADMIN'], $adminUser, 'admin@example.com', $photographeEmail, PriseDeVueVoter::EDIT, $validPriseDeVue, VoterInterface::ACCESS_GRANTED];
        yield 'Admin can DELETE' => [['ROLE_ADMIN'], $adminUser, 'admin@example.com', $photographeEmail, PriseDeVueVoter::DELETE, $validPriseDeVue, VoterInterface::ACCESS_GRANTED];

        // Photographer User cases
        yield 'Photographer can VIEW their PriseDeVue' => [['ROLE_USER'], $photographerUser, $photographeEmail, $photographeEmail, PriseDeVueVoter::VIEW, $validPriseDeVue, VoterInterface::ACCESS_GRANTED];
        yield 'Photographer can EDIT their PriseDeVue' => [['ROLE_USER'], $photographerUser, $photographeEmail, $photographeEmail, PriseDeVueVoter::EDIT, $validPriseDeVue, VoterInterface::ACCESS_GRANTED];
        yield 'Photographer can DELETE their PriseDeVue' => [['ROLE_USER'], $photographerUser, $photographeEmail, $photographeEmail, PriseDeVueVoter::DELETE, $validPriseDeVue, VoterInterface::ACCESS_GRANTED];

        // Other User cases (not the photographer)
        yield 'Other User cannot VIEW photographer PriseDeVue' => [['ROLE_USER'], $otherUser, $otherEmail, $photographeEmail, PriseDeVueVoter::VIEW, $validPriseDeVue, VoterInterface::ACCESS_DENIED];
        yield 'Other User cannot EDIT photographer PriseDeVue' => [['ROLE_USER'], $otherUser, $otherEmail, $photographeEmail, PriseDeVueVoter::EDIT, $validPriseDeVue, VoterInterface::ACCESS_DENIED];
        yield 'Other User cannot DELETE photographer PriseDeVue' => [['ROLE_USER'], $otherUser, $otherEmail, $photographeEmail, PriseDeVueVoter::DELETE, $validPriseDeVue, VoterInterface::ACCESS_DENIED];

        // No user (anonymous)
        yield 'Anonymous cannot VIEW PriseDeVue' => [[], null, '', $photographeEmail, PriseDeVueVoter::VIEW, $validPriseDeVue, VoterInterface::ACCESS_DENIED];

        // --- Cas où `supports()` devrait retourner false, donc `vote()` devrait ABSTAIN ---
        yield 'Unsupported attribute results in ABSTAIN' => [['ROLE_USER'], $photographerUser, $photographeEmail, $photographeEmail, 'UNSUPPORTED_ATTR', $validPriseDeVue, VoterInterface::ACCESS_ABSTAIN];
        yield 'VIEW with non-PriseDeVue subject (stdClass) results in ABSTAIN' => [['ROLE_USER'], $photographerUser, $photographeEmail, $photographeEmail, PriseDeVueVoter::VIEW, new \stdClass(), VoterInterface::ACCESS_ABSTAIN];
        yield 'VIEW with null subject results in ABSTAIN' => [['ROLE_USER'], $photographerUser, $photographeEmail, $photographeEmail, PriseDeVueVoter::VIEW, null, VoterInterface::ACCESS_ABSTAIN];
        yield 'EDIT with null subject results in ABSTAIN' => [['ROLE_USER'], $photographerUser, $photographeEmail, $photographeEmail, PriseDeVueVoter::EDIT, null, VoterInterface::ACCESS_ABSTAIN];
    }
}