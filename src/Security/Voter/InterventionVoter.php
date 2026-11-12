<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Intervention;
use App\Entity\User;
use App\Repository\CustomerRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, Intervention>
 */
class InterventionVoter extends Voter
{
    public const VIEW = 'INTERVENTION_VIEW';
    public const START = 'INTERVENTION_START';
    public const EDIT = 'INTERVENTION_EDIT';
    public const DELETE = 'INTERVENTION_DELETE';
    public const COMPLETE = 'INTERVENTION_COMPLETE';

    public function __construct(private readonly CustomerRepository $customerRepository)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return \in_array(
            $attribute,
            [self::VIEW, self::START, self::EDIT, self::DELETE, self::COMPLETE],
            true
        ) && $subject instanceof Intervention;
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
            self::VIEW => $this->canView($intervention, $user),
            self::START => $user->isAdmin() || $this->isAssignedTo($intervention, $user),
            self::EDIT => $intervention->canBeEdited()
                && ($user->isAdmin() || $this->isAssignedTo($intervention, $user)),
            self::DELETE => $user->isAdmin(),
            self::COMPLETE => Intervention::STATUS_IN_PROGRESS === $intervention->getStatus()
                && ($user->isAdmin() || $this->isAssignedTo($intervention, $user)),
            default => false,
        };
    }

    private function isAssignedTo(Intervention $intervention, User $user): bool
    {
        $mechanic = $intervention->getMechanic();

        return null !== $mechanic && $mechanic->getId() === $user->getId();
    }

    private function canView(Intervention $intervention, User $user): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Mécanicien : uniquement les interventions qui lui sont affectées
        if (\in_array('ROLE_MECHANIC', $user->getRoles(), true)) {
            return $this->isAssignedTo($intervention, $user);
        }

        // Client : uniquement les interventions de ses propres véhicules
        $customer = $this->customerRepository->findOneBy([
            'email' => $user->getUserIdentifier(),
        ]);
        $owner = $intervention->getVehicle()?->getOwner();

        return null !== $customer && null !== $owner && $owner->getId() === $customer->getId();
    }
}