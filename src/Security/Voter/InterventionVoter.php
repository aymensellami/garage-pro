<?php
namespace App\Security\Voter;

use App\Entity\Intervention;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class InterventionVoter extends Voter
{
    public const EDIT = 'INTERVENTION_EDIT';
    public const DELETE = 'INTERVENTION_DELETE';
    public const COMPLETE = 'INTERVENTION_COMPLETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::DELETE, self::COMPLETE])
            && $subject instanceof Intervention;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) return false;

        /** @var Intervention $intervention */
        $intervention = $subject;

        return match ($attribute) {
            self::EDIT => $intervention->canBeEdited() && ($user->isAdmin() || $intervention->getMechanic() === $user),
            self::DELETE => $user->isAdmin(),
            self::COMPLETE => $intervention->getStatus() === Intervention::STATUS_IN_PROGRESS
                && ($user->isAdmin() || $intervention->getMechanic() === $user),
            default => false,
        };
    }
}
