<?php

namespace Database\Seeders;

use App\Models\AccordAppreciationExemple;
use Illuminate\Database\Seeder;

/**
 * Les 4 fiches d'appréciation de référence choisies par la DCUS, transcrites depuis de
 * vraies fiches déjà rédigées (fournies par l'utilisateur) et utilisées comme few-shot fixe
 * par App\Services\AccordAppreciationSuggestionGenerator. Elles ne représentent aucun accord
 * réel dans la table accords — voir App\Models\AccordAppreciationExemple.
 */
class AccordAppreciationExempleSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->exemples() as $exemple) {
            AccordAppreciationExemple::updateOrCreate(['ordre' => $exemple['ordre']], $exemple);
        }
    }

    private function exemples(): array
    {
        return [
            $this->uacIrfp(),
            $this->uacCatf(),
            $this->uacNwafu(),
            $this->uacAlAzhar(),
        ];
    }

    private function uacIrfp(): array
    {
        return [
            'ordre' => 1,
            'origine' => "Université d'Abomey-Calavi (UAC)",
            'objet' => "Etude et avis sur Projet d'accord-cadre de partenariat entre l'Université d'Abomey-Calavi (UAC) et l'Institut Régional de Formation Professionnelle (IRFP).",
            'observations_forme' => <<<'TEXTE'
                Le protocole d'accord-cadre de partenariat comporte l'essentiel des éléments de forme que requièrent les documents de ce type. Sa structuration est conforme aux standards internationaux. Cependant, pour améliorer la qualité du document, il est recommandé :
                - Sur la page de garde : actualiser le mois et l'année de l'accord
                - Dans le document :
                  - écrire « article premier » au lieu de « article 1 »
                  - au niveau de l'article premier alinéa 2 écrire « les parties collaborent » au lieu de « les parties vont collaborer »
                  - revoir l'intitulé du projet afin d'uniformiser la désignation de l'acte, les expressions « accord-cadre de partenariat », « protocole » et « contrat » étant utilisées concurremment
                  - harmoniser la présentation des parties, notamment leurs dénominations, qualités et informations d'identification
                  - revoir la ponctuation, les majuscules et certaines formulations pour une meilleure qualité rédactionnelle
                  - uniformiser les termes employés dans le document, notamment « accord-cadre », « présent accord » et « présent partenariat »
                  - revoir la numérotation et la présentation de certains énumérés afin d'assurer une meilleure lisibilité du document
                TEXTE,
            'observations_fond' => <<<'TEXTE'
                - article premier : reformuler l'objet afin de le rendre plus précis et plus cohérent avec les domaines de coopération énumérés à l'article 2
                - article 2 : remplacer l'expression « domaine du partenariat » par « domaines du partenariat », plusieurs axes de coopération étant prévus
                - article 3 :
                  - remplacer l'intitulé « Apports des parties » par « Engagement des parties » conformément au contenu
                  - harmoniser la formulation des engagements des parties en utilisant l'expression « s'engage à » pour chacune des parties
                - article 7 : préciser la durée de l'obligation de confidentialité, les informations couvertes, les exceptions et les conditions de divulgation autorisée
                - Propriété intellectuelle : insérer une disposition spécifique relative à la propriété, à l'utilisation et à la valorisation des résultats issus des recherches, publications et innovations réalisées dans le cadre du partenariat
                - Article 9 : distinguer clairement les notions de résiliation et de dénonciation et harmoniser le délai de préavis avec les modalités de règlement des projets en cours
                - Article 10 : remplacer le terme « contrat » par « accord-cadre » afin d'assurer l'uniformité de la terminologie du document
                - Dispositions financières : prévoir une clause précisant que chaque partie supporte ses propres charges, sauf stipulation contraire prévue dans des accords spécifiques
                - Force majeure : insérer une disposition relative aux conséquences d'un cas de force majeure sur l'exécution des engagements des parties
                TEXTE,
        ];
    }

    private function uacCatf(): array
    {
        return [
            'ordre' => 2,
            'origine' => "Université d'Abomey-Calavi (UAC)",
            'objet' => "Étude et avis sur le projet d'accord de collaboration entre l'Université d'Abomey-Calavi (UAC) et la Clean Air Task Force (CATF)",
            'observations_forme' => <<<'TEXTE'
                Le projet d'accord de collaboration comporte l'essentiel des éléments de forme requis pour ce type de document. Il est structuré en quatorze (14) articles et précise notamment l'objet de la collaboration, les domaines d'intervention, les engagements des Parties, les règles relatives à la propriété intellectuelle, à la confidentialité, à la durée et aux modalités de résiliation. Cependant, pour améliorer la qualité du document, il est recommandé de :
                - sur la page de garde :
                  - actualiser le mois qui convient sur la page de garde au moment de la signature du document final
                  - faire passer l'Université de Parakou avant la structure partenaire
                - dans la partie liminaire :
                  - au niveau de l'identification des Parties :
                    - renseigner dans la présentation de l'UAC, son acte de création
                    - ajouter l'expression « d'une part » après la dénomination de la première université et « d'autre part » avant la désignation de la seconde partie
                    - préciser pour CATF, l'identité et le titre du représentant habilité à signer le document
                    - reformuler le paragraphe relatif à la dénomination des Parties comme suit : « L'Université d'Abomey-Calavi et Clean Air Task Force pris individuellement est dénommé la « Partie » et collectivement les « Parties » »
                    - compléter l'expression « ci-après dénommée » à la fin de la présentation de la Clean Air Task Force
                  - préambule :
                    - actualiser la dénomination du ministère de tutelle comme suit : « Ministère de l'Enseignement supérieur et de la Recherche scientifique, en charge de la Formation technique »
                    - renseigner la référence de l'avis du ministère avant la signature du document
                    - écrire « il a été convenu de ce qui suit » au lieu de « les parties ont convenu de ce qui suit »
                - dans le corps du document :
                  - article 4 : terminer chaque énumération des engagements des parties par un point-virgule et l'harmoniser pour l'ensemble du document
                  - article 5 :
                    - identifier pour chacune des Parties, les structures ou les responsables ainsi que leur adresse chargée de siéger au sein du Comité de suivi
                    - revoir l'intitulé en « Comité de suivi de l'accord »
                  - article 3 : revoir l'intitulé de l'article en « Modalité de mise en œuvre de la collaboration »
                  - corriger l'erreur de numérotation constatée au niveau des articles 12 et 13 (deux dispositions portent le numéro douze)
                  - enlever le soulignement des articles dans l'ensemble du document
                  - harmoniser l'utilisation des termes « Accord », « Protocole d'accord » et « MoU » employés dans le document
                  - procéder à une relecture générale afin d'améliorer la cohérence rédactionnelle et la présentation du document
                TEXTE,
            'observations_fond' => <<<'TEXTE'
                Le projet d'accord poursuit un objectif pertinent de coopération universitaire et scientifique entre les deux institutions. Les domaines de coopération envisagés sont variés et cohérents avec les missions des Parties. Le contenu du partenariat présente ainsi une réelle valeur académique et institutionnelle. Cependant, les dispositions suivantes sont recommandées pour permettre la mise en œuvre efficiente du projet :
                - uniformiser le temps verbal en recourant au présent de l'indicatif dans l'ensemble du document, en lieu et place du futur simple
                - prévoir une clause sur les engagements financiers des parties (Dispositions financières)
                - article 8 : clarifier la portée du partage de responsabilité prévu pour les personnels recrutés spécifiquement dans le cadre du Programme, afin de mieux déterminer les obligations respectives des Parties
                - article 10 :
                  - préciser davantage les mécanismes applicables en cas d'échec du règlement amiable avant toute résiliation de l'accord
                  - corriger la dernière phrase de l'article 10 en précisant que le présent accord sera résilié conformément aux dispositions de l'article 12 et non de l'article 11, car la résiliation de l'accord est prévue par l'article 12
                - article 13 : qui soumet l'accord aux lois du Commonwealth du Massachusetts, appelle une attention particulière au regard du statut juridique de l'Université d'Abomey-Calavi ; il conviendrait d'apprécier la compatibilité de cette disposition avec les règles applicables aux établissements publics béninois
                - supprimer au niveau de l'article 13, l'alinéa relatif à la modification et l'ajouter à l'article 12 qui parle de la résiliation, puis reformuler le titre de l'article 12 comme suit : « Résiliation et Modification »
                TEXTE,
        ];
    }

    private function uacNwafu(): array
    {
        return [
            'ordre' => 3,
            'origine' => "Université d'Abomey-Calavi (UAC)",
            'objet' => "Etude et avis sur le protocole d'accord pour le projet de Laboratoire Conjoint Sino-Béninois en Nutrition Avicole de Précision et le Développement des Ressources Alimentaires Locales",
            'observations_forme' => <<<'TEXTE'
                Le projet du protocole d'accord comporte l'essentiel des éléments de forme que requièrent les documents de ce type. Sa structuration est globalement cohérente et conforme aux standards internationaux. Cependant, pour améliorer la qualité du document, il est recommandé :
                - Le document est intitulé « Protocole d'Accord » alors que plusieurs dispositions (article 15 notamment) précisent qu'il s'agit uniquement d'une déclaration d'intention non juridiquement contraignante. Il conviendrait donc d'utiliser l'intitulé « Memorandum of Understanding » ou « Protocole d'entente »
                - préambule : contrairement aux bonnes pratiques administratives des accords interinstitutionnels, le document ne comporte pas de préambule juridique faisant référence :
                  - aux missions des deux institutions
                  - à leur volonté de renforcer la coopération scientifique
                  - etc.
                - dans le corps du document :
                  - le texte emploie alternativement « Laboratoire conjoint » et « Laboratoire mixte » ; ces expressions devraient être uniformisées
                  - remplacer l'expression « les deux Parties » par « les Parties » dans tout le texte
                  - corriger ces quelques imperfections :
                    - « Le développement » au lieu de « La développement »
                    - « soutiendront financièrement chaque année » au lieu de « soutiendront financièrement annuel »
                    - « consultation mutuelle » au lieu de « consultation Mutuelle »
                    - « renforcer » au lieu de « renforçer »
                  - revoir la mise en forme (erreurs de syntaxe, d'orthographe, de ponctuation, uniformisation d'espacement entre les mots, les paragraphes et entre les articles, passages difficilement lisibles, etc.) dans tout le document avant sa signature
                  - renseigner le nom du représentant de la NWAFU dans le bloc de signature avant la signature du document
                  - article 13 : compléter pour chaque partie, le statut juridique, le représentant légal (Recteur/président), et la qualité du signataire, ce qui renforce la sécurité juridique de l'accord
                  - l'article 18 indique que l'accord est établi en chinois et en anglais, alors que le document analysé est rédigé en français ; préciser par exemple que le présent Accord est établi en chinois, en français et en anglais, les trois versions faisant également foi, ou indiquer explicitement laquelle prévaut en cas de divergence
                - créer une page de garde comportant le logo de chaque Partie, l'objet du protocole, le mois et l'année de signature du protocole
                - compléter à la présentation des parties la formule « Entre, l'Université…, d'une part, Et l'Université…, d'autre part. »
                - écrire que l'Université d'Abomey-Calavi et l'Université d'Agriculture et de Foresterie du Nord-Ouest sont ci-après dénommées individuellement une « Partie » et collectivement les « Parties »
                - remplacer « les deux Parties conviennent de ce qui suit : » par « Il a été convenu de ce qui suit : »
                - distinguer le préliminaire (présentation des Parties) du préambule
                - structurer le corps du document en articles, en remplaçant la numérotation actuelle (1 à 18) par une numérotation sous forme d'articles (Article premier, Article 2, …, Article 18)
                - certaines lignes du document sont illisibles et ne permettent pas de faire une analyse minutieuse des parties concernées
                - commencer les énumérations par une lettre minuscule et les terminer par un point-virgule
                - éviter l'usage cumulatif des puces (•) et des chiffres « (1) ; (2) » au niveau des énumérations
                - utiliser le présent de l'indicatif dans tout le corps du document
                - éviter l'utilisation de la conjonction de coordination inclusive « et/ou » en choisissant « et » ou « ou »
                - harmoniser l'orthographe des termes constituant l'intitulé du projet, l'utilisation des majuscules et des minuscules étant inappropriée
                - harmoniser l'orthographe de l'expression « Laboratoire Conjoint », la majuscule et la minuscule de la lettre « c » étant utilisées de façon incohérente
                - supprimer, à l'article 13, au niveau des informations relatives à l'Université d'Abomey-Calavi, la mention « (p.4) »
                TEXTE,
            'observations_fond' => <<<'TEXTE'
                Le projet de candidature poursuit un objectif pertinent de coopération universitaire et scientifique entre les deux institutions. Cependant, les dispositions suivantes sont recommandées pour permettre la mise en œuvre efficiente du projet :
                - article 3 : le contenu évoque la mobilité du personnel, sans préciser :
                  - les conditions d'accueil
                  - la couverture médicale
                  - les assurances
                  - les visas
                  - la prise en charge financière
                  - la sécurité des chercheurs
                - article 4 : le contenu indique simplement que le montant sera déterminé par consultation mutuelle ; il serait souhaitable de bien détailler les modalités financières en précisant :
                  - les contributions respectives des parties
                  - les dépenses éligibles
                  - les modalités de gestion des fonds
                  - les règles d'audit
                - article 10 : le texte fait référence à un Comité de gestion, mais ne précise pas sa composition, sa mission, ni les modalités de prise de décision ; ces éléments devraient faire l'objet d'un article spécifique
                - article 12 :
                  - le contenu prévoit l'arbitrage mais ne précise pas le droit applicable, le siège de l'arbitrage, ni la langue de l'arbitrage
                  - prévoir un alinéa pour préciser la gestion des projets en cours en cas de résiliation, ces éléments étant utiles en cas de différend
                - aucune disposition ne traite la gestion des risques non prévue, notamment les cas de force majeure, les pandémies, les catastrophes naturelles
                - le laboratoire interviendra dans la recherche scientifique ; il serait opportun d'ajouter une clause imposant le respect des règles d'éthique de la recherche, des réglementations sanitaires, des normes internationales relatives aux expérimentations
                - renseigner les références de l'avis donné par le Ministère avant la signature du document
                - créer un article relatif à l'objet de l'accord
                - article premier : il ressort de la lecture de la première phrase que le développement du projet de Laboratoire conjoint vise à promouvoir l'« élargissement des sources de financement » ; reformuler cette expression de manière plus précise afin d'éviter toute ambiguïté
                - article 2 : incohérence entre l'intitulé de l'article et son contenu ; reformuler le contenu de façon à mettre l'accent sur le caractère nécessaire des conditions
                - fusionner les articles 3 et 9 puis regrouper leur contenu sous un même article intitulé « Obligations des Parties »
                - fusionner les articles 4 et 11 afin d'assurer une bonne cohérence du texte
                - fusionner les articles 7 et 16 puis regrouper leur contenu sous un même article intitulé « Propriété intellectuelle, Confidentialité et Protection des données »
                - article 7 : harmoniser la dernière phrase de la seconde énumération de cet article avec l'article 12 relatif au règlement des différends
                - article 14 : reformuler cet article comme suit : « Le présent Accord est conclu pour une durée de cinq (5) ans, renouvelable par accord entre les Parties. »
                - fusionner la seconde phrase de l'article 14 et la troisième énumération de l'article 16 et en faire un article distinct intitulé « Résiliation »
                - reformuler l'article 18 comme suit : « Le présent Accord est établi en six (6) exemplaires originaux dont deux (2) en langue chinoise, deux (2) en langue française et deux (2) en langue anglaise. Chaque Partie conserve un exemplaire de chacune des versions linguistiques. En cas de divergence, d'incohérence ou de contradiction entre versions, la version anglaise fait foi. Le présent Accord entre en vigueur à compter de sa date de signature par les représentants dûment habilités des Parties. »
                TEXTE,
        ];
    }

    private function uacAlAzhar(): array
    {
        return [
            'ordre' => 4,
            'origine' => "Université d'Abomey-Calavi (UAC)",
            'objet' => "Etude et avis sur le protocole d'accord de coopération entre l'Université d'Abomey-Calavi (UAC), Bénin et le Centre de développement de l'enseignement destiné aux étudiants internationaux et étrangers à AL-Azhar",
            'observations_forme' => <<<'TEXTE'
                Le protocole d'accord de coopération comporte l'essentiel des éléments de forme que requièrent les documents de ce type. Sa structuration est conforme aux standards internationaux. Le préambule, l'objet de la coopération, la durée ainsi que la procédure de renouvellement sont clairement mentionnés. Cependant, pour améliorer la qualité du document, il est recommandé :
                - Prévoir une page de garde comportant :
                  - en haut de page les logos des structures contractantes
                  - l'intitulé formulé comme suit : « Protocole d'accord de coopération », puis sur une nouvelle ligne « Entre ». Dans un nouveau paragraphe, indiquer l'Université d'Abomey-Calavi, première partenaire, puis un paragraphe « Et » avant d'indiquer la structure partenaire, le Centre de développement de l'enseignement destiné aux étudiants internationaux et étrangers à Al-Azhar
                  - en bas de page, mentionner l'année et le mois dans lequel le document a été signé
                - Dans la partie liminaire :
                  - au niveau de la présentation des parties, commencer par le titre « protocole d'accord de coopération » puis « Entre ». Indiquer l'Université d'Abomey-Calavi, son adresse complète et les informations permettant de l'identifier, puis un paragraphe « D'une part » aligné à droite, un paragraphe « Et » avant la structure partenaire, suivie de « D'autre part » également à droite en fin de sa présentation complète
                  - au niveau de la présentation des parties, mettre les vraies dénominations pour chaque partie (ex : ci-après dénommé « UAC » pour l'Université d'Abomey-Calavi au lieu de « la première Partie »)
                  - juste après l'identification des parties et avant le préambule, écrire que « les deux parties sont appelées collectivement « les Parties » et individuellement « la Partie » »
                  - dans le préambule :
                    - ramener le quatrième paragraphe comme dernier paragraphe du préambule et renseigner les références de l'avis du Ministère avant la signature dudit protocole
                    - mettre l'expression « les parties conviennent ce qui suit : » du dernier paragraphe du préambule sur une nouvelle ligne
                    - actualiser la dénomination du ministère de l'enseignement supérieur
                    - écrire « les parties ont convenu de ce qui suit » au lieu de « les parties conviennent de ce qui suit »
                    - remplacer le groupe de mots « les deux parties » par « les parties » et en tenir rigoureusement compte dans le document
                  - article 3 :
                    - premier puce : écrire « apprenants » au lieu de « apprenenants »
                    - cinquième puce : supprimer le point qui est de trop
                  - article 4 : écrire « l'exécution » au lieu de « I'exécution »
                - Dans le corps du document :
                  - ramener l'article intitulé « objectifs » juste après l'article premier
                  - article 2 : au niveau du titre, mettre « engagement des parties » au lieu de « engagement » tout court
                  - remplacer dans le document les expressions « la première Partie » par « l'Université Al-Azhar » et « la seconde Partie » par « l'UAC »
                  - absence du timbre du partenaire ainsi que de ses coordonnées complètes (adresse postale, contacts téléphoniques et adresse électronique)
                  - nécessité de présenter l'Université d'Abomey-Calavi avant le Centre de Développement de l'enseignement de la langue arabe pour les non-arabophones, conformément aux règles protocolaires applicables aux actes conclus par l'Université
                TEXTE,
            'observations_fond' => <<<'TEXTE'
                Le projet de protocole d'accord de coopération poursuit un objectif pertinent de coopération universitaire et scientifique entre les deux institutions. Les domaines de coopération envisagés sont cohérents avec les missions des Parties. Le contenu du protocole présente ainsi une réelle valeur académique et institutionnelle. Cependant, les dispositions suivantes sont recommandées pour permettre la mise en œuvre efficiente du projet :
                - la formulation des objectifs est à revoir afin d'éviter de les transformer en engagements contraignants implicites ; ils doivent être reformulés dans un style programmatique et non impératif (formulation à éviter, ex : « former les apprenants » sans nuancer, « garantir l'amélioration du niveau », etc.). Proposition : « le présent protocole a pour objectif de promouvoir la coopération entre les parties dans les domaines de la formation et du renforcement des capacités académiques, notamment à travers la formation des apprenants, le développement des compétences des étudiants ainsi que l'amélioration du niveau de maîtrise de la langue arabe »
                - fusionner les contenus des articles 2 et 6 (engagements et responsabilités) en un seul article intitulé « Responsabilités des parties », puis utiliser l'expression « les deux parties conviennent de collaborer … » au lieu de « les deux parties s'engagent… »
                - article 7 : supprimer cet article et ramener son contenu comme un alinéa dans l'article 11
                - article 11 : changer son titre en « Durée, renouvellement et dénonciation »
                - article 13 :
                  - changer le titre en « dispositions finales »
                  - prévoir au niveau de la partie signature, un champ pour mentionner la date de signature de chaque représentant de partie
                - prévoir parmi les clauses un article sur la Résiliation en définissant les conditions qui encadrent cette alternative
                - prévoir un article sur la date de prise d'effet du protocole
                - Préambule :
                  - remplacer le premier paragraphe par la rédaction suivante : « la langue arabe occupe une place importante parmi les langues de communication, d'enseignement, de recherche et de diffusion des connaissances. Porteuse d'un riche patrimoine culturel et intellectuel, elle contribue au renforcement des échanges académiques, scientifiques et interculturels à l'échelle internationale »
                  - reformuler le troisième paragraphe ainsi : « considérant que l'Université d'Abomey-Calavi, à travers l'Institut de Langue Arabe et de la Culture Islamique (ILACI), s'emploie à promouvoir l'enseignement de la langue arabe et de la culture islamique, ainsi qu'à développer et structurer des parcours de formation répondant aux besoins éducatifs, académiques et professionnels des apprenants en République du Bénin »
                  - article premier : reformuler le contenu de cet article afin qu'il réponde à l'objet du protocole, puis ramener l'actuel contenu dans un nouvel article intitulé « dispositions diverses »
                  - article 4 :
                    - identifier expressément les responsables de la coopération désignés par chacune des parties
                    - préciser les attributions du comité de suivi prévu en complétant la disposition concernée par la formule « Ce comité de suivi a notamment pour mission de… »
                  - article 5 : reformuler cet article de sorte que la supervision technique soit assurée par les Parties et non une Partie
                  - fusionner les contenus des articles 2 et 6 (engagements et responsabilités) en un seul article intitulé « engagements des Parties », puis reformuler dans un langage cohérent les engagements de chaque Partie
                  - préciser à l'article 6 alinéa 2, la portée de l'engagement relatif à l'exonération fiscale, aux permis de travail et aux titres de séjour, notamment les catégories de personnes concernées, la nature des impôts visés et les modalités de prise en charge des formalités administratives
                  - article 8 : dans la mesure où l'enseignement est assuré dans un État souverain doté de son propre calendrier universitaire et de ses spécificités académiques, il s'est avéré nécessaire de réviser le contenu du présent article afin de le mettre en conformité avec le contexte béninois
                  - article 10 : prévoir un alinéa relatif à la composition du comité conjoint chargé de régler les différends entre les Parties
                  - créer un article relatif à la résiliation du présent protocole de coopération puis prévoir une disposition relative aux projets en cours d'exécution avant la résiliation du projet
                  - article 13 : il prévoit que seule la version arabe fait foi en cas de divergence entre les versions linguistiques du protocole ; à défaut de justification particulière, il conviendrait de prévoir que les deux versions feront foi
                  - créer un article relatif aux modalités financières
                TEXTE,
        ];
    }
}
