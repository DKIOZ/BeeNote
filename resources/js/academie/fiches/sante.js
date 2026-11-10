// Fiches santé et réserves. Aucune dose de traitement n'est donnée ici :
// seuls les médicaments autorisés comptent, et leur notice fait foi.

export default [
    {
        id: 'varroa',
        theme: 'sante',
        mois: [7, 8, 12],
        signes: ['varroas_observes:ok'],
        voir: 'Varroas présents',
        titre: 'Varroas visibles : la colonie est déjà très touchée',
        titreDetail: 'Comprendre le varroa',
        lecture: 7,
        enBref: 'Un varroa visible sur une abeille en cache beaucoup d\'autres dans le couvain.',
        urgent: 'en fin d\'été : c\'est là que se préparent les abeilles qui passeront l\'hiver.',
        gestes: [
            'Notez la ruche concernée et regardez si des abeilles ont les ailes déformées.',
            'Comptez les varroas : lange graissé sous la ruche, ou test au sucre glace.',
            'Traitez avec un médicament autorisé, selon sa notice, et notez-le au registre.',
        ],
        eviter: 'traiter avec des hausses posées, ou avec un produit non autorisé.',
        detail: [
            {
                titre: 'Un parasite présent partout',
                texte: [
                    'Le varroa est un petit acarien rouge qui se nourrit des abeilles et se reproduit dans le couvain. Il affaiblit la colonie et transmet des virus. Presque toutes les colonies en France en portent : la question n\'est pas de savoir s\'il est là, mais combien.',
                ],
            },
            {
                titre: 'Mesurer avant de traiter',
                liste: [
                    ['Le lange graissé', 'une plaque glissée sous le plancher grillagé recueille les varroas qui tombent. On compte la chute sur quelques jours.'],
                    ['Le test au sucre glace', 'un pot d\'abeilles roulées dans le sucre libère leurs varroas, qu\'on compte sans tuer les abeilles.'],
                ],
            },
            {
                titre: 'Quand traiter',
                texte: [
                    'Le traitement principal se fait juste après la dernière récolte, en été. Beaucoup d\'apiculteurs ajoutent un traitement d\'hiver, quand la colonie n\'a plus de couvain.',
                    'Utilisez uniquement un médicament vétérinaire autorisé pour les abeilles, et suivez sa notice. Le traitement doit être noté dans le registre d\'élevage : BeeNote le fait avec la visite.',
                ],
            },
            {
                titre: 'Se faire aider',
                texte: ['Le groupement de défense sanitaire apicole (GDSA) de votre département conseille sur les produits et le bon moment. Il organise souvent des commandes groupées.'],
            },
        ],
        liees: ['colonie-faible', 'hivernage'],
    },
    {
        id: 'frelon',
        theme: 'sante',
        mois: [8, 9, 10],
        signes: ['frelons_asiatiques:quelques_uns', 'frelons_asiatiques:nombreux'],
        voir: 'Frelons à l\'entrée',
        titre: 'Frelons asiatiques : protéger l\'entrée',
        titreDetail: 'Le frelon asiatique',
        lecture: 5,
        enBref: 'Les frelons chassent devant l\'entrée. Les abeilles stressées ne sortent plus butiner.',
        urgent: 'si les abeilles restent bloquées à l\'intérieur : la colonie s\'affame.',
        gestes: [
            'Réduisez l\'entrée de la ruche, ou posez une muselière (grille devant l\'entrée).',
            'Gardez des colonies fortes : elles se défendent mieux.',
            'Un nid repéré ? Signalez-le à votre mairie, sans y toucher.',
        ],
        eviter: 'détruire un nid vous-même : c\'est dangereux et souvent inefficace.',
        detail: [
            {
                titre: 'Un prédateur de fin de saison',
                texte: ['Le frelon asiatique chasse surtout de la fin de l\'été à l\'automne, quand son nid a besoin de protéines. Il vole en place devant l\'entrée et attrape les butineuses au retour.'],
            },
            {
                titre: 'Protéger le rucher',
                liste: [
                    ['La muselière', 'une grille posée devant l\'entrée gêne le vol stationnaire du frelon.'],
                    ['Le piégeage', 'au printemps, il vise les fondatrices. Utilisez un piège sélectif, qui laisse sortir les autres insectes.'],
                    ['Le signalement', 'la destruction des nids est faite par des professionnels ; beaucoup de communes la prennent en charge.'],
                ],
            },
        ],
        liees: ['colonie-faible', 'hivernage'],
    },
    {
        id: 'fausse-teigne',
        theme: 'sante',
        mois: [6, 7, 8, 9],
        signes: ['fausse_teigne:ok'],
        voir: 'Fausse teigne',
        titre: 'Fausse teigne : retirer la cire abandonnée',
        titreDetail: 'La fausse teigne',
        lecture: 4,
        enBref: 'Des chenilles creusent des galeries dans la cire. Elles profitent surtout des colonies faibles.',
        gestes: [
            'Retirez les cadres que les abeilles n\'occupent plus.',
            'Resserrez la ruche avec une partition, à la taille de la colonie.',
            'Cherchez pourquoi la colonie est faible : reine, varroa, réserves.',
        ],
        eviter: 'laisser des cadres de cire vide dans une ruche faible ou au grenier sans protection.',
        detail: [
            {
                titre: 'Un signe plutôt qu\'une maladie',
                texte: ['Une colonie forte nettoie elle-même ses cadres. Quand la fausse teigne s\'installe, c\'est souvent que la colonie n\'a plus assez d\'abeilles pour surveiller toute la ruche.'],
            },
            {
                titre: 'Stocker les cadres',
                texte: ['Hors de la ruche, gardez les cadres au froid, à la lumière et aérés, écartés les uns des autres. Un passage au congélateur tue les œufs et les chenilles.'],
            },
        ],
        liees: ['colonie-faible'],
    },
    {
        id: 'nourrir',
        theme: 'sante',
        mois: [9, 10, 2, 3],
        signes: ['reserves_miel:insuffisantes'],
        voir: 'Réserves faibles',
        titre: 'Réserves faibles : nourrir la colonie',
        titreDetail: 'Bien nourrir ses abeilles',
        lecture: 5,
        enBref: 'Une colonie sans réserves peut mourir de faim, surtout à la fin de l\'hiver.',
        urgent: 'en hiver ou au début du printemps : la faim tue en quelques jours.',
        gestes: [
            'Soupesez la ruche par l\'arrière pour juger son poids.',
            'Par temps doux, en automne : du sirop. Par temps froid : du candi, posé sur les cadres.',
            'Nourrissez le soir, ruche fermée, sans renverser de sucre dehors.',
        ],
        eviter: 'donner du sirop avec les hausses posées : il finirait dans votre miel.',
        detail: [
            {
                titre: 'Quelle nourriture, quand',
                liste: [
                    ['Le sirop épais', 'en début d\'automne, pour compléter les réserves d\'hiver. Les abeilles le stockent.'],
                    ['Le candi ou la pâte', 'en hiver, quand il fait trop froid pour un liquide. Les abeilles le consomment au fur et à mesure.'],
                    ['Le sirop léger', 'au printemps, pour relancer la ponte, à petites doses.'],
                ],
            },
            {
                titre: 'Combien de réserves pour l\'hiver',
                texte: ['Une colonie a besoin d\'une bonne quinzaine de kilos de miel pour passer l\'hiver, davantage dans un climat froid. Avec l\'habitude, on le sent en soulevant l\'arrière de la ruche.'],
            },
        ],
        liees: ['hivernage', 'colonie-faible'],
    },
];
