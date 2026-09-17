<?php
namespace App\Entity;

use App\Repository\MaintenanceAlertRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MaintenanceAlertRepository::class)]
#[ORM\HasLifecycleCallbacks]
class MaintenanceAlert
{
    public const TYPE_TECHNICAL_CONTROL = 'technical_control';
    public const TYPE_OIL_CHANGE = 'oil_change';
    public const TYPE_TIMING_BELT = 'timing_belt';
    public const TYPE_BRAKES = 'brakes';
    public const TYPE_TIRES = 'tires';
    public const TYPE_BATTERY = 'battery';
    public const TYPE_CUSTOM = 'custom';

    public const SEVERITY_INFO = 'info';
    public const SEVERITY_WARNING = 'warning';
    public const SEVERITY_DANGER = 'danger';
    public const SEVERITY_CRITICAL = 'critical';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'maintenanceAlerts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Vehicle $vehicle = null;

    #[ORM\Column(length: 50)]
    private ?string $type = null;

    #[ORM\Column(length: 30)]
    private ?string $severity = self::SEVERITY_INFO;

    #[ORM\Column(length: 255)]
    private ?string $message = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $triggeredAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $resolvedAt = null;

    #[ORM\ManyToOne]
    private ?User $resolvedBy = null;

    #[ORM\Column]
    private bool $isResolved = false;

    public function __construct()
    {
        $this->triggeredAt = new \DateTime();
    }

    public function getId(): ?int { return $this->id; }
    public function getVehicle(): ?Vehicle { return $this->vehicle; }
    public function setVehicle(?Vehicle $vehicle): static { $this->vehicle = $vehicle; return $this; }
    public function getType(): ?string { return $this->type; }
    public function setType(string $type): static { $this->type = $type; return $this; }
    public function getSeverity(): ?string { return $this->severity; }
    public function setSeverity(string $severity): static { $this->severity = $severity; return $this; }
    public function getMessage(): ?string { return $this->message; }
    public function setMessage(string $message): static { $this->message = $message; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }
    public function getTriggeredAt(): ?\DateTimeInterface { return $this->triggeredAt; }
    public function getResolvedAt(): ?\DateTimeInterface { return $this->resolvedAt; }
    public function getResolvedBy(): ?User { return $this->resolvedBy; }
    public function isResolved(): bool { return $this->isResolved; }

    public function resolve(User $user): static
    {
        $this->isResolved = true;
        $this->resolvedAt = new \DateTime();
        $this->resolvedBy = $user;
        return $this;
    }

    public function getTypeLabel(): string
    {
        return match($this->type) {
            self::TYPE_TECHNICAL_CONTROL => 'Contrôle technique',
            self::TYPE_OIL_CHANGE => 'Vidange',
            self::TYPE_TIMING_BELT => 'Courroie de distribution',
            self::TYPE_BRAKES => 'Freins',
            self::TYPE_TIRES => 'Pneus',
            self::TYPE_BATTERY => 'Batterie',
            default => 'Alerte personnalisée',
        };
    }

    public function getSeverityColor(): string
    {
        return match($this->severity) {
            self::SEVERITY_INFO => '#3b82f6',
            self::SEVERITY_WARNING => '#f59e0b',
            self::SEVERITY_DANGER => '#ef4444',
            self::SEVERITY_CRITICAL => '#7f1d1d',
            default => '#64748b',
        };
    }

    public function isUrgent(): bool
    {
        return in_array($this->severity, [self::SEVERITY_DANGER, self::SEVERITY_CRITICAL]);
    }
}
