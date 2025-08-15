<?php

namespace App\Pdf\Trait;

trait JoinFilePdfTrait
{
    /**
     * Liste des fichiers attachés
     */
    protected array $joinedFiles = [];

    /**
     * Numéro courant d'objet de type fichier
     */
    private int $nJoinedFile = 0;

    /**
     * Ajoute un fichier à la liste des fichiers
     * @param string $filename Chemin absolu du fichier
     * @param ?string $name Nom du fichier
     * @param ?string $desc Description du fichier
     */
    public function addFile(string $filename, ?string $name = '', ?string $desc = ''): self
    {
        if (!file_exists($filename)) {
            $this->Error('Fichier introuvable : ' . $filename);
        }
        array_push($this->joinedFiles, [
            'file' => $filename,
            'name' => (empty($name)) ? basename($filename) : $name,
            'size' => filesize($filename),
            'desc' => $desc,
        ]);
        return $this;
    }

    /**
     * Appelée par _putresources pour ajouter les fichiers joints
     */
    private function putFiles(): void
    {
        foreach ($this->joinedFiles as $i => &$info) {
            $file = $info['file'];
            $name = $info['name'];
            $desc = $info['desc'];

            $fc = file_get_contents($file);
            $size = strlen($fc);
            $date = @date('YmdHisO', filemtime($file));
            $md = 'D:' . substr($date, 0, -2) . "'" . substr($date, -2) . "'";;

            $this->_newobj();
            $info['n'] = $this->n;
            $this->_put('<<');
            $this->_put('/Type /Filespec');
            $this->_put('/F (' . $this->_escape($name) . ')');
            $this->_put('/UF ' . $this->_textstring($name));
            $this->_put('/EF <</F ' . ($this->n + 1) . ' 0 R>>');
            if ($desc) {
                $this->_put('/Desc ' . $this->_textstring($desc));
            }
            $this->_put('/AFRelationship /Unspecified');
            $this->_put('>>');
            $this->_put('endobj');

            $this->_newobj();
            $this->_put('<<');
            $this->_put('/Type /EmbeddedFile');
            $this->_put('/Subtype /application#2Foctet-stream');
            $this->_put('/Length ' . $size);
            $this->_put('/Params <</Size ' . $size . ' /ModDate ' . $this->_textstring($md) . '>>');
            $this->_put('>>');
            $this->_putstream($fc);
            $this->_put('endobj');
        }
        unset($info);

        $this->_newobj();
        $this->nJoinedFile = $this->n;
        $a = [];
        foreach ($this->joinedFiles as $i => $info) {
            $a[] = $this->_textstring(sprintf('%03d', $i)) . ' ' . $info['n'] . ' 0 R';
        }
        $this->_put('<<');
        $this->_put('/Names [' . join(' ', $a) . ']');
        $this->_put('>>');
        $this->_put('endobj');
    }
}
