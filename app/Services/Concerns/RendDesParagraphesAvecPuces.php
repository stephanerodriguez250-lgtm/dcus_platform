<?php

namespace App\Services\Concerns;

use PhpOffice\PhpWord\Element\AbstractContainer;

/**
 * Une ligne commençant par « - », « • » ou « * » devient un point de liste ; le nombre
 * d'espaces avant ce marqueur détermine le sous-niveau (2 espaces = un niveau plus bas).
 * Une ligne sans marqueur reste un simple paragraphe. Le conteneur peut être une Section
 * ou une Cell de tableau — les deux exposent addText()/addListItem() via AbstractContainer.
 */
trait RendDesParagraphesAvecPuces
{
    private function ajouterParagraphes(AbstractContainer $conteneur, ?string $texte): void
    {
        $lignes = array_filter(explode("\n", (string) $texte), fn ($ligne) => trim($ligne) !== '');

        if ($lignes === []) {
            $conteneur->addText('—');

            return;
        }

        foreach ($lignes as $ligne) {
            if (preg_match('/^(\s*)[-•*]\s+(.+)$/', $ligne, $correspondances)) {
                $niveau = min(intdiv(strlen(str_replace("\t", '  ', $correspondances[1])), 2), 3);
                $conteneur->addListItem(trim($correspondances[2]), $niveau);
            } else {
                $conteneur->addText(trim($ligne));
            }
        }
    }
}
