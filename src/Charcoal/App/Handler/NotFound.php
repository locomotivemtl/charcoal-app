<?php

namespace Charcoal\App\Handler;

// Dependencies from PSR-7 (HTTP Messaging)
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;

// Dependency from Slim
use \Slim\Http\Body;

// Dependency from 'charcoal-translation'
use \Charcoal\Translation\Catalog\CatalogInterface;

// Local Dependencies
use \Charcoal\App\Handler\AbstractHandler;

/**
 * Not Found Handler
 *
 * Enhanced version of {@see \Slim\Handlers\NotAllowed}.
 *
 * It outputs a simple message in either JSON, XML,
 * or HTML based on the Accept header.
 */
class NotFound extends AbstractHandler
{
    /**
     * Invoke "Not Found" Handler
     *
     * @param  ServerRequestInterface $request  The most recent Request object.
     * @param  ResponseInterface      $response The most recent Response object.
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response)
    {
        $contentType = $this->determineContentType($request);
        switch ($contentType) {
            case 'application/json':
                $output = $this->renderJsonOutput();
                break;

            case 'text/xml':
            case 'application/xml':
                $output = $this->renderXmlOutput();
                break;

            case 'text/html':
            default:
                $output = $this->renderHtmlOutput();
        }

        $body = new Body(fopen('php://temp', 'r+'));
        $body->write($output);

        return $response->withStatus(404)
                        ->withHeader('Content-Type', $contentType)
                        ->withBody($body);
    }

    /**
     * Render Plain/Text Error
     *
     * @return string
     */
    protected function renderPlainOutput()
    {
        $message = $this->catalog()->translate('not-found');

        return $this->render($message);
    }

    /**
     * Render JSON Error
     *
     * @return string
     */
    protected function renderJsonOutput()
    {
        $message = $this->catalog()->translate('not-found');

        return $this->render('{"message":"'.$message.'"}');
    }

    /**
     * Render XML Error
     *
     * @return string
     */
    protected function renderXmlOutput()
    {
        $message = $this->catalog()->translate('not-found');

        return $this->render('<root><message>'.$message.'</message></root>');
    }

    /**
     * Render title of error
     *
     * @return string
     */
    public function messageTitle()
    {
        return $this->catalog()->entry('page-not-found');
    }

    /**
     * Render body of HTML error
     *
     * @return string
     */
    public function renderHtmlMessage()
    {
        $title = $this->messageTitle();
        $link  = sprintf(
            '<a href="%1$s">%2$s</a>',
            $this->baseUrl(),
            $this->catalog()->entry('visit-home-page')
        );
        $notice  = $this->catalog()->entry('page-not-found-description');
        $message = '<h1>'.$title."</h1>\n\t\t<p>".$notice."</p>\n\t\t<p>".$link."</p>\n";

        return $message;
    }

    /**
     * Sets a translation catalog instance on the object.
     *
     * @param  CatalogInterface $catalog A translation catalog object.
     * @return NotFound Chainable
     */
    public function setCatalog(CatalogInterface $catalog)
    {
        parent::setCatalog($catalog);

        $messages = [
            'page-not-found' => [
                'en' => 'Page Not Found',
                'fr' => 'Page Introuvable',
                'es' => 'Página No Encontrada'
            ],
            'not-found' => [
                'en' => 'Not Found',
                'fr' => 'Introuvable',
                'es' => 'No Encontrado'
            ],
            'visit-home-page' => [
                'en' => 'Visit the Home Page',
                'fr' => 'Visitez la page d’accueil',
                'es' => 'Visita la página de inicio'
            ],
            'page-not-found-description' => [
                'en' => 'The page you are looking for could not be found. '
                       .'Check the address bar to ensure your URL is spelled correctly. '
                       .'If all else fails, you can visit our home page at the link below.',
                'fr' => 'La page que vous recherchez n’a pu être trouvée. '
                       .'Vérifiez la barre d’adresse pour assurer que votre URL est correctement orthographié. '
                       .'Si rien ne fonctionne, vous pouvez visiter notre page d’accueil au lien ci-dessous.',
                'es' => 'La página que estás buscando no se pudo encontrar. '
                       .'Compruebe la barra de direcciones para asegurarse de que su URL está escrito correctamente. '
                       .'Si todo lo demás falla, se puede visitar la página de inicio en el siguiente enlace.'
            ]
        ];

        foreach ($messages as $key => $entry) {
            if (!$this->catalog()->hasEntry($key)) {
                $this->catalog()->addEntry($key, $entry);
            }
        }

        return $this;
    }
}
