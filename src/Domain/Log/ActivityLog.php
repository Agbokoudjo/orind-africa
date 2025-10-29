<?php

declare(strict_types=1);

/*
 * This file is part of the project by AGBOKOUDJO Franck.
 *
 * (c) AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * Phone: +229 01 67 25 18 86
 * LinkedIn: https://www.linkedin.com/in/internationales-web-apps-services-120520193/
 * Github: https://github.com/Agbokoudjo/
 * Company: INTERNATIONALES WEB APPS & SERVICES
 *
 * For more information, please feel free to contact the author.
 */

namespace App\Domain\Log;

use App\Domain\Log\Enum\ActivityAction;
use App\Domain\ModelTrait\CreatedAtTrait;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
abstract class ActivityLog  
{
    use CreatedAtTrait ;
    protected readonly array $userContext;
    protected int|string|null $id;
    protected  readonly string $ipAddress ;
    protected  readonly ActivityAction $action ;
    protected  readonly  string $method;
    protected readonly string $route ;
    protected  readonly array $context  ;

    /**
     * Get the value of id
     */
    public function getId(): int|string
    {
        return $this->id;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(string $ipAddress): static
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }

    public function getAction(): ?ActivityAction
    {
        return $this->action;
    }

    public function setAction(ActivityAction $action): static
    {
        $this->action = $action;

        return $this;
    }

    public function getRoute(): ?string
    {
        return $this->route;
    }

    public function setRoute(string $route): static
    {
        $this->route = $route;

        return $this;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function setContext(array $context): static
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Get the value of method
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Set the value of method
     */
    public function setMethod(string $method): self
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Get the value of userContext
     */
    public function getUserContext(): array
    {
        return $this->userContext;
    }

    /**
     * Set the value of userContext
     */
    public function setUserContext(array $userContext): self
    {
        $this->userContext = $userContext;

        return $this;
    }
}

