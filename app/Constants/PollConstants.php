<?php

namespace App\Constants;

/**
 * Constantes relacionadas a votações.
 */
class PollConstants
{
    /**
     * Tamanho padrão do pódio.
     */
    public const DEFAULT_PODIUM_SIZE = 3;

    /**
     * Status da votação.
     */
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    /**
     * Multiplicador de pontos por posição do pódio.
     */
    public const POINTS_MULTIPLIER = 2;

    /**
     * Pontos base para a última posição.
     */
    public const BASE_POINTS = 1;
}
