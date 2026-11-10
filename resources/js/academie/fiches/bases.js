// Fiches de base et gestes de l'année : sans lien avec une observation,
// elles remontent selon le mois.

export default [
    {
        id: 'ouvrir',
        theme: 'bases',
        mois: [],
        signes: [],
        titre: 'Ouvrir une ruche sans stress',
        titreDetail: 'Ouvrir une ruche sans stress',
        lecture: 4,
        enBref: 'Beau temps, gestes lents, enfumoir prêt : la visite se passe bien pour vous et les abeilles.',
        gestes: [
            'Choisissez un jour doux et sans vent, plutôt en milieu de journée.',
            'Allumez l\'enfumoir : une fumée blanche et froide, quelques bouffées à l\'entrée.',
            'Placez-vous sur le côté ou derrière, jamais devant l\'entrée.',
            'Bougez lentement, et refermez en moins de quinze minutes.',
        ],
        eviter: 'ouvrir par temps froid, sous la pluie ou à l\'approche d\'un orage.',
        detail: [
            {
                titre: 'S\'équiper',
                texte: ['Un voile ou une vareuse, des gants, un lève-cadre et un enfumoir suffisent pour commencer. Des vêtements clairs et lisses énervent moins les abeilles.'],
            },
            {
                titre: 'Savoir ce qu\'on cherche',
                texte: ['Avant d\'ouvrir, décidez ce que vous venez voir : la ponte, les réserves, la place. Une visite courte et ciblée dérange moins la colonie qu\'un démontage complet.'],
            },
        ],
        liees: ['colonie-agressive'],
    },
    {
        id: 'hausse',
        theme: 'bases',
        mois: [4, 5, 6],
        signes: [],
        titre: 'Poser une hausse au bon moment',
        titreDetail: 'Poser et retirer les hausses',
        lecture: 4,
        enBref: 'La hausse donne de la place pour le miel. Trop tôt elle refroidit la ruche, trop tard la colonie essaime.',
        gestes: [
            'Posez-la quand les abeilles occupent presque tous les cadres du corps et que les fleurs donnent.',
            'Une seule hausse à la fois : la suivante quand celle-ci est bien remplie.',
            'Retirez les hausses avant tout traitement contre le varroa.',
        ],
        eviter: 'poser une hausse sur une colonie faible : elle ne pourra pas la chauffer.',
        detail: [
            {
                titre: 'Lire les signes',
                texte: ['Des abeilles sur tous les cadres, de la cire blanche fraîche sur le haut des cadres, des rentrées de nectar : c\'est le moment. En plaine, cela arrive souvent en avril ; en montagne, plus tard.'],
            },
            {
                titre: 'La grille à reine',
                texte: ['Placée entre le corps et la hausse, elle empêche la reine d\'y pondre. Elle n\'est pas obligatoire, mais elle simplifie la récolte quand on débute.'],
            },
        ],
        liees: ['essaimage'],
    },
    {
        id: 'hivernage',
        theme: 'annee',
        mois: [10, 11, 12, 1],
        signes: [],
        titre: 'Préparer l\'hiver : réserves, entrée, traitement',
        titreDetail: 'Faire passer l\'hiver à ses colonies',
        lecture: 6,
        enBref: 'En hiver, on n\'ouvre plus : tout se prépare avant le froid.',
        gestes: [
            'Soupesez chaque ruche : trop légère, donnez du candi.',
            'Réduisez l\'entrée et posez une grille contre les souris.',
            'Calez le toit avec une pierre, la ruche un peu penchée vers l\'avant.',
        ],
        eviter: 'ouvrir la ruche par temps froid : la colonie perd sa chaleur.',
        detail: [
            {
                titre: 'La grappe d\'hiver',
                texte: ['Quand il fait froid, les abeilles se serrent en boule autour de la reine et produisent de la chaleur en mangeant leurs réserves. Une ouverture casse cette boule.'],
            },
            {
                titre: 'Le traitement d\'hiver',
                texte: ['En plein hiver, la colonie n\'a souvent plus de couvain : les varroas sont tous sur les abeilles. Beaucoup d\'apiculteurs en profitent pour un traitement autorisé, selon sa notice.'],
            },
            {
                titre: 'Surveiller sans ouvrir',
                texte: ['Regardez l\'entrée par une journée douce : des abeilles qui sortent, c\'est bon signe. Soupesez encore la ruche en février.'],
            },
        ],
        liees: ['nourrir', 'varroa'],
    },
    {
        id: 'declaration',
        theme: 'annee',
        mois: [9, 10, 11, 12],
        signes: [],
        titre: 'Déclarer ses ruches avant le 31 décembre',
        titreDetail: 'La déclaration annuelle des ruches',
        lecture: 3,
        enBref: 'Chaque année, toute personne qui possède des ruches les déclare, même pour une seule.',
        gestes: [
            'Entre le 1er septembre et le 31 décembre, déclarez vos ruches en ligne.',
            'Indiquez le nombre de colonies et l\'emplacement de vos ruchers.',
            'Gardez votre numéro d\'apiculteur (NAPI) : il se met sur les ruches.',
        ],
        detail: [
            {
                titre: 'Pourquoi déclarer',
                texte: ['La déclaration aide les services sanitaires à suivre les maladies des abeilles et à prévenir les apiculteurs proches en cas de problème. Elle est obligatoire dès la première colonie.'],
            },
            {
                titre: 'Où la faire',
                texte: ['Sur le site du ministère de l\'Agriculture, rubrique déclaration de ruches. La première fois, vous obtenez votre numéro d\'apiculteur (NAPI).'],
            },
            {
                titre: 'Le registre d\'élevage',
                texte: ['À côté de la déclaration, la loi demande de tenir un registre : visites, traitements, mouvements de ruches. BeeNote le remplit au fil de vos visites.'],
            },
        ],
        liees: ['hivernage'],
    },
];
