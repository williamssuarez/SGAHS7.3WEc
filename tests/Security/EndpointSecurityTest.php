<?php

namespace App\Tests\Security;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Repository\UserRepository;

class EndpointSecurityTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        // Authenticate as an admin/doctor to access the endpoints
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('marialessandra3n@gmail.com');
        $this->client->loginUser($testUser);
    }

    /**
     * Test de inyecciones SQL Generico
     * @return void
     */
    /*public function testPatientSearchResistsSqlInjection(): void
    {
        // A classic payload designed to terminate a string and drop a table
        $maliciousPayload = "O'Brian'; DROP TABLE paciente; --";

        // Send the payload to the search endpoint
        $this->client->request('GET', '/hospitalizacion/censo', [
            'search' => $maliciousPayload
        ]);

        // If the developer bypassed Doctrine and concatenated this string directly into
        // createNativeQuery(), it will crash the DB and return a 500 Error.
        // If they used parameterized queries properly, it returns 200 OK (finding 0 results).
        $this->assertResponseIsSuccessful();
    }*/

    /**
     * Test de Cross-site scripting XSS Generica
     * @return void
     */
    /*public function testPatientNameResistsCrossSiteScripting(): void
    {
        $xssPayload = "<script>alert('hack');</script>";

        // 1. Submit the malicious payload to a form
        $this->client->request('POST', '/paciente/nuevo', [
            'paciente' => [
                'nombre' => $xssPayload,
                'apellido' => 'Doe',
                'cedula' => '12345678',
                '_token' => $this->generateCsrfToken() // Assuming you have a helper for this
            ]
        ]);

        // 2. Navigate to a view where that data is displayed (e.g., the Censo or Expediente)
        $this->client->request('GET', '/pacientes');

        $responseContent = $this->client->getResponse()->getContent();

        // 3. Assert the script tag was NOT rendered as raw HTML
        $this->assertStringNotContainsString($xssPayload, $responseContent);

        // 4. Assert Twig successfully auto-escaped it
        $this->assertStringContainsString('&lt;script&gt;alert(&#039;hack&#039;);&lt;/script&gt;', $responseContent);
    }*/

    /**
     * Test de filtro raw de twig generica
     * @return void
     */
    public function testNoUnsafeRawFiltersInTwig(): void
    {
        $twigDir = __DIR__ . '/../../templates';
        $output = shell_exec("grep -r '|raw' $twigDir");

        // If this fails, a developer added |raw to a template.
        // You should manually review if it's safe (e.g., rendering trusted HTML from a WYSIWYG)
        $this->assertNull($output, "Found potential XSS vulnerability: |raw filter used in Twig templates:\n" . $output);
    }
}
