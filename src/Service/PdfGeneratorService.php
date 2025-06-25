<?php

namespace App\Service;

use Dompdf\Dompdf;
use Symfony\Component\HttpFoundation\Response;

/**
 * Service de génération de fichiers PDF
 * 
 * Pattern appliqué: Service Layer Pattern (patterns.md)
 * Responsabilité unique: Génération de documents PDF
 */
class PdfGeneratorService
{
    public function __construct()
    {
    }

    /**
     * Génère une réponse PDF à partir d'un contenu HTML
     */
    public function generatePdfResponse(string $html, string $filename, array $options = []): Response
    {
        // Créer une instance de Dompdf avec les options par défaut
        $dompdf = new Dompdf();
        
        // Configurer les options directement sur l'instance
        $dompdf->getOptions()->setDefaultFont($options['font'] ?? 'Arial');
        $dompdf->getOptions()->setIsRemoteEnabled(true);
        $dompdf->getOptions()->setIsHtml5ParserEnabled(true);
        
        $dompdf->loadHtml($html);
        $dompdf->setPaper($options['paper'] ?? 'A4', $options['orientation'] ?? 'portrait');
        $dompdf->render();

        $disposition = $options['disposition'] ?? 'inline';
        
        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => sprintf('%s; filename="%s"', $disposition, $filename)
        ]);
    }

    /**
     * Génère le contenu PDF sous forme de string
     */
    public function generatePdfString(string $html, array $options = []): string
    {
        // Créer une instance de Dompdf avec les options par défaut
        $dompdf = new Dompdf();
        
        // Configurer les options directement sur l'instance
        $dompdf->getOptions()->setDefaultFont($options['font'] ?? 'Arial');
        $dompdf->getOptions()->setIsRemoteEnabled(true);
        $dompdf->getOptions()->setIsHtml5ParserEnabled(true);
        
        $dompdf->loadHtml($html);
        $dompdf->setPaper($options['paper'] ?? 'A4', $options['orientation'] ?? 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }
} 