// Fiches sur la colonie et la reine. Chaque fiche a deux niveaux :
// le terrain (lu en 30 secondes, ruche ouverte) et le détail (lu à la maison).
// « signes » relie la fiche aux observations de la visite (champ:valeur de fields.js).

export default [
    {
        id: 'essaimage',
        theme: 'colonie',
        mois: [4, 5, 6],
        signes: ['cellules_royales:ok'],
        voir: 'Cellules royales',
        titre: 'Cellules royales : la colonie veut essaimer',
        titreDetail: 'Comprendre l\'essaimage',
        lecture: 6,
        enBref: 'Une partie des abeilles va partir avec la reine. Agir dans la semaine garde la colonie.',
        urgent: 'si les cellules sont fermées (operculées) : l\'essaim peut partir dans 1 à 2 jours.',
        gestes: [
            'Regardez dedans : une larve dans de la gelée blanche, c\'est sérieux. Vide, c\'est une simple ébauche.',
            'Reine vue ? Faites un essaim artificiel : la reine et 2 à 3 cadres dans une ruchette.',
            'Donnez de la place et revenez dans 7 jours.',
        ],
        eviter: 'casser toutes les cellules et repartir. La colonie recommence, ou reste sans reine.',
        detail: [
            {
                titre: 'Pourquoi les abeilles partent',
                texte: [
                    'L\'essaimage est la façon naturelle dont une colonie se multiplie. Quand la ruche manque de place et que la population explose au printemps, les abeilles élèvent de nouvelles reines. La vieille reine part alors avec une grande partie des butineuses.',
                    'Pour l\'apiculteur, c\'est souvent une récolte perdue et une colonie affaiblie.',
                ],
            },
            {
                titre: 'Essaimage ou remplacement ?',
                texte: [
                    'Plusieurs cellules pendues au bas des cadres annoncent souvent un essaimage. Une ou deux cellules au milieu d\'un cadre signalent plutôt une reine que la colonie remplace.',
                    'C\'est un repère, pas une règle : regardez aussi la force de la colonie et la saison.',
                ],
            },
            {
                titre: 'Les solutions',
                liste: [
                    ['L\'essaim artificiel', 'la reine part dans une ruchette avec quelques cadres ; la souche élève une nouvelle reine. Pour débuter.'],
                    ['Donner de la place à temps', 'hausse ou cadres à bâtir avant que la ruche soit pleine, dès avril. C\'est la prévention.'],
                    ['La méthode Demaree', 'séparer la reine du couvain dans la même ruche, avec une grille. Pour apiculteur confirmé.'],
                ],
            },
            {
                titre: 'L\'essaim est déjà parti ?',
                texte: ['Une nouvelle reine va naître. Laissez la ruche tranquille environ trois semaines, puis cherchez des œufs : c\'est le signe qu\'elle pond.'],
            },
        ],
        liees: ['hausse', 'pas-de-ponte'],
    },
    {
        id: 'pas-de-ponte',
        theme: 'colonie',
        mois: [3, 4, 5, 6, 7, 8],
        signes: ['ponte_observee:non_ok'],
        voir: 'Pas de ponte',
        titre: 'Pas de ponte : la reine est peut-être absente',
        titreDetail: 'Une colonie sans reine',
        lecture: 5,
        enBref: 'Sans œufs à la belle saison, la colonie a peut-être perdu sa reine. En hiver, c\'est normal.',
        urgent: 'si vous ne voyez ni œufs ni jeunes larves depuis deux visites.',
        gestes: [
            'Cherchez des œufs au fond des cellules, face au soleil : un petit bâtonnet blanc, debout.',
            'Plusieurs œufs par cellule, posés sur les parois ? Ce sont des ouvrières qui pondent : demandez conseil.',
            'Rien du tout ? Donnez un cadre avec des œufs d\'une autre ruche, et revenez dans 7 jours.',
        ],
        eviter: 'conclure trop vite : une jeune reine peut mettre deux à trois semaines avant de pondre.',
        detail: [
            {
                titre: 'Pourquoi la ponte s\'arrête',
                texte: [
                    'La reine peut être morte, écrasée pendant une visite ou partie avec un essaim. La ponte s\'arrête aussi naturellement en hiver, pendant une sécheresse ou quand une jeune reine n\'a pas encore été fécondée.',
                ],
            },
            {
                titre: 'Le cadre test',
                texte: [
                    'Prenez dans une autre colonie un cadre avec des œufs et de très jeunes larves. Si la colonie y construit des cellules royales, elle n\'a plus de reine : elle va en élever une. Si elle n\'en construit pas, une reine est probablement là.',
                ],
            },
            {
                titre: 'Les ouvrières pondeuses',
                texte: [
                    'Restée trop longtemps sans reine, une colonie peut voir des ouvrières pondre. Elles ne donnent que des mâles : le couvain est bombé et irrégulier. La situation est difficile à rattraper, souvent par une réunion avec une colonie saine.',
                ],
            },
        ],
        liees: ['essaimage', 'colonie-faible'],
    },
    {
        id: 'colonie-faible',
        theme: 'colonie',
        mois: [],
        signes: ['estimation_population:faible'],
        voir: 'Colonie faible',
        titre: 'Colonie faible : l\'aider à reprendre',
        titreDetail: 'Comprendre une colonie faible',
        lecture: 5,
        enBref: 'Peu d\'abeilles, c\'est une colonie fragile : au pillage, au froid et aux parasites.',
        urgent: 'à l\'automne : une colonie trop faible ne passera pas l\'hiver seule.',
        gestes: [
            'Réduisez l\'entrée pour qu\'elle se défende contre le pillage.',
            'Regardez la reine, la ponte et les réserves : la cause est souvent là.',
            'Resserrez la ruche avec une partition, et ouvrez-la moins souvent.',
        ],
        eviter: 'la nourrir au sirop en plein jour, ruche ouverte : le sucre attire le pillage.',
        detail: [
            {
                titre: 'Les causes fréquentes',
                texte: ['Une reine qui pond mal ou plus du tout, une forte présence de varroas, le manque de nourriture, un essaimage récent ou un pillage par les ruches voisines.'],
            },
            {
                titre: 'Réunir deux colonies',
                texte: [
                    'À l\'automne, une colonie trop faible gagne à être réunie à une autre. On pose une feuille de papier journal percée de petits trous entre les deux corps : les abeilles la rongent en quelques jours et s\'habituent à l\'odeur de l\'autre colonie.',
                ],
            },
        ],
        liees: ['pas-de-ponte', 'varroa', 'nourrir'],
    },
    {
        id: 'colonie-agressive',
        theme: 'colonie',
        mois: [],
        signes: ['humeur_colonie:agressive'],
        voir: 'Colonie agressive',
        titre: 'Colonie agressive : refermer et revenir',
        titreDetail: 'Pourquoi une colonie devient agressive',
        lecture: 4,
        enBref: 'Une colonie nerveuse a souvent une raison passagère : la météo, la faim ou un geste brusque.',
        urgent: 'si des personnes ou des animaux sont près des ruches : éloignez-les.',
        gestes: [
            'Refermez calmement, sans écraser d\'abeilles : leur odeur excite les autres.',
            'Éloignez-vous lentement de quelques mètres, à l\'ombre.',
            'Revenez un jour de beau temps, entre 11 h et 16 h, enfumoir bien allumé.',
        ],
        eviter: 'taper sur la ruche ou faire de grands gestes : cela empire tout.',
        detail: [
            {
                titre: 'Les causes passagères',
                texte: ['Un orage qui approche, le froid, la fin d\'une miellée, un pillage en cours, une visite trop longue. La plupart du temps, la colonie redevient calme quelques jours plus tard.'],
            },
            {
                titre: 'Quand ça dure',
                texte: ['Une colonie qui reste agressive visite après visite peut manquer de reine, ou avoir une reine à la descendance nerveuse. Le changement de reine (le remérage) règle souvent le problème : demandez conseil à un apiculteur proche.'],
            },
        ],
        liees: ['ouvrir', 'pas-de-ponte'],
    },
];
