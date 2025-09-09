<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class MakeServiceCommand extends Command
{
    protected $signature = 'make:service {name : Le nom du service}';
    protected $description = 'Créer un nouveau service';

    public function handle()
    {
        $name = $this->argument('name');
        
        // Ajouter "Service" si pas déjà présent
        if (!str_ends_with($name, 'Service')) {
            $name .= 'Service';
        }

        $filesystem = new Filesystem();
        
        // Créer le dossier Services s'il n'existe pas
        $serviceDir = app_path('Services');
        if (!$filesystem->exists($serviceDir)) {
            $filesystem->makeDirectory($serviceDir);
        }

        // Chemin du fichier
        $path = $serviceDir . '/' . $name . '.php';

        // Vérifier si le fichier existe déjà
        if ($filesystem->exists($path)) {
            $this->error("Le service {$name} existe déjà !");
            return 1;
        }

        // Contenu du template
        $template = $this->getTemplate($name);

        // Créer le fichier
        $filesystem->put($path, $template);

        $this->info("Service {$name} créé avec succès !");
        $this->line("Chemin : app/Services/{$name}.php");

        return 0;
    }

    private function getTemplate(string $name): string
    {
        return "<?php

namespace App\\Services;

class {$name}
{
    /**
     * Exemple de méthode service
     */
    public static function example(): void
    {
        // Implémentation à venir...
    }
}
";
    }
}