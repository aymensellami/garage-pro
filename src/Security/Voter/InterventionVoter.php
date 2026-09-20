<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Intervention;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, Intervention>
 */
class InterventionVoter extends Voter
{
    public const EDIT = 'INTERVENTION_EDIT';
    public const DELETE = 'INTERVENTION_DELETE';
    public const COMPLETE = 'INTERVENTION_COMPLETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return \in_array($attribute, [self::EDIT, self::DELETE, self::COMPLETE], true)
            && $subject instanceof Intervention;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        /** @var Intervention $intervention */
        $intervention = $subject;

        return match ($attribute) {
            self::EDIT => $intervention->canBeEdited() && ($user->isAdmin() || $intervention->getMechanic() === $user),
            self::DELETE => $user->isAdmin(),
            self::COMPLETE => Intervention::STATUS_IN_PROGRESS === $intervention->getStatus()
                && ($user->isAdmin() || $intervention->getMechanic() === $user),
            default => false,
        };
    }
}