<?php

declare(strict_types=1);

/*
 * This file is part of the project by AGBOKOUDJO Franck.
 *
 * (c) AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * Phone: +229 01 67 25 18 86
 * LinkedIn: https://www.linkedin.com/in/internationales-web-apps-services-120520193/
 * Github: https://github.com/Agbokoudjo/
 * Company: INTERNATIONALES WEB APPS & SERVICES
 *
 * For more information, please feel free to contact the author.
 */

namespace App\Infrastructure\Security\BrowserValidation;

use Symfony\Component\HttpFoundation\Request;

/**
 * Validateur de requêtes navigateur
 * 
 * Bloque les requêtes provenant de :
 * - curl, wget, curl, postman
 * - Scripts automatisés
 * - Outils de pentest (burp, zaproxy, etc)
 * - Bots et crawlers
 * 
 * Validateur avancé de requêtes navigateur
 * 
 * Détecte les simulations de navigateur :
 * - curl avec User-Agent spooféd
 * - Headers suspendu
 * - Absences de cookies
 * - Timing anormal
 * - Fingerprinting TLS
 * 
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 */
final class BrowserRequestValidator
{
    /**
     * User-Agents des outils à bloquer
     */
    private const BLOCKED_USER_AGENTS = [
        // Outils de ligne de commande
        'curl',
        'wget',
        'python-requests',
        'httpie',
        'postman',

        // Outils de pentest
        'burp',
        'zaproxy',
        'nmap',
        'masscan',
        'sqlmap',
        'nikto',
        'nessus',
        'metasploit',
        'wpscan',
        'dirbuster',

        // Bots et crawlers
        'bot',
        'crawler',
        'spider',
        'scraper',
        'slurp',
        'googlebot',
        'bingbot',

        // Autres
        'java',
        'python',
        'ruby',
        'perl',
        'node.js',
        'go-http-client',
        'zgrab',
        'node',
        'golang',
    ];

    /**
     * Headers qui doivent être présents dans une requête navigateur
     */
    private const REQUIRED_BROWSER_HEADERS = [
        'Accept',
        'Accept-Language',
        'Accept-Encoding',
        'User-Agent',
        'Sec-Fetch-Site',
    ];

    /**
     * Valide si la requête provient d'un navigateur réel
     * 
     * @return true si c'est une requête valide de navigateur
     * @return false si c'est une requête suspecte
     */
    public function isValidBrowserRequest(Request $request): bool
    {
        // 1. Vérifier le User-Agent
        if (!$this->hasValidUserAgent($request)) {
            return false;
        }

        // 2. Vérifier les headers typiques du navigateur
        if (!$this->hasRequiredBrowserHeaders($request)) {
            return false;
        }

        // 3. Vérifier les caractéristiques de la requête
        if (!$this->hasValidBrowserCharacteristics($request)) {
            return false;
        }
        // Vérifier la cohérence User-Agent/Accept-Language
        if (!$this->isUserAgentLanguageConsistent($request)) {
            return false;
        }

         //Vérifier la cohérence User-Agent/Accept-Encoding
        if (!$this->isUserAgentEncodingConsistent($request)) {
            return false;
        }

         // Détection de simulation
        if ($this->isSimulatedBrowser($request)) {
            return false;
        }

        return true;
    }

     /**
     * Détecte les simulations de navigateur classiques
     */
    private function isSimulatedBrowser(Request $request): bool
    {
        // 3. Les headers de sécurité modernes (Sec-Fetch-*)
        $hasFetchHeaders = $request->headers->has('Sec-Fetch-Site') &&
            $request->headers->has('Sec-Fetch-Mode') &&
            $request->headers->has('Sec-Fetch-Dest');

        // Les navigateurs modernes envoient ces headers (sauf curl)
        if (!$hasFetchHeaders) {  return false;}

        // 3. Vérifier l'ordre des headers
        if (!$this->hasValidHeaderOrder($request)) {
            return true;
        }
        // 4. Vérifier les cookies
        if ($request->cookies->count() === 0 && $request->getMethod() !== 'GET') {
            // Les navigateurs envoient généralement des cookies
            // (sauf si c'est la première requête)
            return true;
        }

        return false;
    }

    /**
     * Vérifie l'ordre des headers (les vrais navigateurs ont un ordre spécifique)
     */
    private function hasValidHeaderOrder(Request $request): bool
    {
        $headers = $request->headers->keys();

        // L'ordre typique des headers d'un navigateur :
        // Host, User-Agent, Accept, Accept-Language, Accept-Encoding, Connection, etc.

        // curl simule souvent un ordre différent
        $userAgentIndex = array_search('user-agent', array_map('strtolower', $headers), true);
        $acceptIndex = array_search('accept', array_map('strtolower', $headers), true);

        if ($userAgentIndex === false || $acceptIndex === false) {
            return false;
        }

        // User-Agent devrait être avant Accept dans un vrai navigateur
        if ($userAgentIndex > $acceptIndex) {
            return true; // Suspicious
        }

        return false;
    }

    /**
     * Vérifie que le User-Agent n'est pas suspecte
     */
    private function hasValidUserAgent(Request $request): bool
    {
        $userAgent = $request->headers->get('User-Agent', '');

        // User-Agent manquant = suspecte
        if (empty($userAgent)) {
            return false;
        }

        $userAgentLower = strtolower($userAgent);

        // Vérifier contre la liste des outils bloqués
        foreach (self::BLOCKED_USER_AGENTS as $blockedAgent) {
            if (str_contains($userAgentLower, strtolower($blockedAgent))) {
                return false;
            }
        }

        // Vérifier que c'est un navigateur connu (Chrome, Firefox, Safari, Edge)
        $browserPatterns = [
            'chrome|firefox|safari|edge|opera|trident',
        ];

        foreach ($browserPatterns as $pattern) {
            if (preg_match("/$pattern/i", $userAgent)) {
                return true;
            }
        }

        // User-Agent ne correspond à aucun navigateur connu
        return false;
    }

    /**
     * Vérifie la présence des headers typiques d'un navigateur
     */
    private function hasRequiredBrowserHeaders(Request $request): bool
    {
        foreach (self::REQUIRED_BROWSER_HEADERS as $header) {
            if (!$request->headers->has($header)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Vérifie les caractéristiques supplémentaires d'une requête navigateur
     */
    private function hasValidBrowserCharacteristics(Request $request): bool
    {
        // Les navigateurs envoient toujours un Referer ou Origin
        // (sauf pour les requêtes de première navigation)
        $hasReferer =$request->headers->has('referer') || $request->headers->has('origin');
        $isFirstNavigation = $request->headers->has('accept-encoding') || $request->headers->has('Accept-Encoding');
        $accept = $request->headers->get('Accept', '');
        if (!$hasReferer && !$isFirstNavigation) {
            // Peut être suspecte, mais on laisse passer pour la première requête
            // Vérifier au moins qu'il y a un Accept valide
            if (empty($accept) || $accept === '*/*') {
                return false;
            }
        }
       
        if(!$request->isXmlHttpRequest()){
             if (stripos($accept, 'text/html') === false) {
                return false;
            }
        }

        if (in_array($request->getMethod(), ['POST', 'PUT', 'DELETE','PATCH','CONNECT'], true)) {
            $referer=$request->headers->get('Referer','') ;
            $origin=$request->headers->get('origin','');
            if (empty($referer) && empty($origin)) {
                return false; // Peut être OK si c'est une API, mais suspicious pour une requête de navigateur
            }
        }

        return true;
    }

    /**
     * Vérifie la cohérence User-Agent/Accept-Encoding
     */
    private function isUserAgentEncodingConsistent(Request $request): bool
    {
        $acceptEncoding = $request->headers->get('Accept-Encoding', '');

        // Les navigateurs envoient toujours gzip, deflate, br, etc
        if (empty($acceptEncoding)) {
            return false;
        }

        // curl peut mettre "gzip, deflate, br" mais manque de caractéristiques d'ordre
        // Les vrais navigateurs ont un ordre spécifique selon le moteur
        $hasGzip = stripos($acceptEncoding, 'gzip') !== false;
        $hasDeflate = stripos($acceptEncoding, 'deflate') !== false;

        // Au moins gzip ou deflate
        if (!$hasGzip && !$hasDeflate) {
            return false;
        }

        return true;
    }

    /**
     * Vérifie la cohérence User-Agent/Accept-Language
     */
    private function isUserAgentLanguageConsistent(Request $request): bool
    {
        $acceptLanguage = $request->headers->get('Accept-Language', '');
        // Si le UA est un vrai navigateur, Accept-Language ne devrait pas être vide
        if (empty($acceptLanguage)) {
            return false;
        }

        // Format typique : "fr,fr-FR;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6"
        // Ou : "en-US,en;q=0.9"
        // Ou : "fr-FR"

        // Le regex accepte :
        // - 2 lettres minuscules (la langue)
        // - Optionnellement : tiret + 2 lettres majuscules (la région)
        // - Optionnellement : ;q=0.X (le poids)

        // Exemple : "fr-FR;q=0.9" ou "en" ou "en-US;q=0.8"
        if (!preg_match('/^[a-z]{2}(-[A-Z]{2})?/', $acceptLanguage)) {
            return false;
        }

        // Vérifier le format de la liste (si plusieurs langues)
         $languages = explode(',', $acceptLanguage);
        if(!empty($lanages)){
            foreach ($languages as $lang) {
                $lang = trim($lang);

                // Chaque langue doit respecter le format : "xx" ou "xx-XX" ou "xx-XX;q=0.X"
                if (!preg_match('/^[a-z]{2}(-[A-Z]{2})?(\;q=0\.\d+)?$/', $lang)) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Retourne un message d'erreur explicite
     */
    public function getBlockReasonMessage(Request $request): string
    {
        $userAgent = $request->headers->get('User-Agent', 'Non fourni');

        if (empty($userAgent)) {
            return 'Requête rejetée : User-Agent manquant. Accès réservé aux navigateurs.';
        }

        if (!$this->hasValidUserAgent($request)) {
            return sprintf(
                'Requête rejetée : User-Agent "%s" n\'est pas autorisé. Accès réservé aux navigateurs.',
                $userAgent
            );
        }

        if (!$this->hasRequiredBrowserHeaders($request)) {
            return 'Requête rejetée : Headers obligatoires manquants. Accès réservé aux navigateurs.';
        }

        if ($this->isSimulatedBrowser($request)) {
            return 'Simulation de navigateur détectée (curl, wget, etc.).';
        }

        return 'Requête rejetée : Caractéristiques de requête invalides. Accès réservé aux navigateurs.';
    }
}
