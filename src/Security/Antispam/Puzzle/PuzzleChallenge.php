<?php

namespace App\Security\Antispam\Puzzle;

use App\Security\Antispam\ChallengeInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;

final class PuzzleChallenge implements ChallengeInterface
{
    public const WIDTH = 320;
    public const HEIGHT = 220;
    public const PIECE_WIDTH = 80;
    public const PIECE_HEIGHT = 50;
    private const SESSION_KEY = 'puzzles';
    private const PRECISION = 2;
    private Session $session;

    public function __construct(private readonly RequestStack $requestStack)
    {
        $this->session = is_null($this->requestStack->getMainRequest())
            ? new Session(new MockFileSessionStorage())
            : $this->requestStack->getSession();
    }

    /**
     * Génère une clé stockée en session et la retourne
     */
    public function generateKey(): string
    {
        $now = time();
        $x = mt_rand(0, self::WIDTH - self::PIECE_WIDTH);
        $y = mt_rand(0, self::HEIGHT - self::PIECE_HEIGHT);
        $puzzles = $this->session->get(self::SESSION_KEY, []);
        $puzzles[] = ['key' => $now, 'solution' => [$x, $y]];
        $this->session->set(self::SESSION_KEY, array_slice($puzzles, -10));
        return $now;
    }

    /**
     * Retourne la solution à partir d'une clé
     * 
     * @param string $key La clé dont il faut retourner la solution
     * @return int[]|null
     */
    public function getSolution(string $key): array|null
    {
        $puzzles = $this->session->get(self::SESSION_KEY, []);
        foreach ($puzzles as $puzzle) {
            if ($puzzle['key'] !== intval($key)) {
                continue;
            }
            return $puzzle['solution'];
        }
        return null;
    }

    /**
     * Vérifie la que la réponse correspond à la clé
     * 
     * @param string $key La clé à vérifier
     * @param string $answer Réponse à vérifier
     */
    public function verify(string $key, string $answer): bool
    {
        $expected = $this->getSolution($key);
        if (!$expected) {
            return false;
        }

        // Suppression du puzzle de la session
        $puzzles = $this->session->get(self::SESSION_KEY);
        $this->session->set(self::SESSION_KEY, array_filter($puzzles, fn(array $puzzle) => $puzzle['key'] !== intval($key)));

        $got = $this->stringToPosition($answer);
        return abs($expected[0] - $got[0]) <= self::PRECISION
            && abs($expected[1] - $got[1]) <= self::PRECISION;
    }

    /**
     * Retourne un tableau de deux valeurs à partir de la réponse
     * 
     * @param string $s Réponse à parser
     * @return int[]
     */
    private function stringToPosition(string $s): array
    {
        $parts = explode('-', $s, 2);
        if (count($parts) !== 2) {
            return [-1, -1];
        }
        return [intval($parts[0]), intval($parts[1])];
    }
}
