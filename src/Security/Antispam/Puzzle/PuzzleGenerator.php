<?php

namespace App\Security\Antispam\Puzzle;

use App\Security\Antispam\ChallengeGeneratorInterface;
use App\Utils\Images;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;

final class PuzzleGenerator implements ChallengeGeneratorInterface
{
    public function __construct(
        private readonly PuzzleChallenge $challenge,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $root,
    ) {}

    /**
     * Génère un puzzle
     * 
     * @param string $key Clé du puzzle
     */
    public function generate(string $key): Response
    {
        $position = $this->challenge->getSolution($key);
        if (!$position) {
            return new Response('Derche !', Response::HTTP_NOT_FOUND);
        }

        [$x, $y] = $position;

        // Image random
        $backgroundPath = $this->getRandomImage();
        $image = Images::make($backgroundPath)->resize($this->challenge::WIDTH, $this->challenge::HEIGHT);

        // Puzzle piece
        $piecePath = sprintf('%s/assets/images/puzzle/puzzle-piece.png', $this->root);
        $piece = Images::make($piecePath)->resize($this->challenge::PIECE_WIDTH, $this->challenge::PIECE_HEIGHT);

        $hole = clone $piece;
        $piece
            ->insert($image, 'top-left', -$x, -$y)
            ->mask($hole, true);

        $image
            ->resizeCanvas(
                PuzzleChallenge::PIECE_WIDTH,
                0,
                'left',
                true,
                'rgba(0, 0, 0, 0)'
            )
            ->insert($piece, 'top-right')
            ->insert($hole->opacity(60), 'top-left', $x, $y);

        return $image->response('png');
    }

    /**
     * Retourne un chemin de fichier image de manière aléatoire
     */
    private function getRandomImage(): string
    {
        $backgrounds = glob(sprintf('%s/assets/images/puzzle/camaro-*.png', $this->root));
        return $backgrounds[array_rand($backgrounds, 1)];
    }
}
