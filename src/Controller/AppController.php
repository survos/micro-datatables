<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\RegisterFormDto;
use App\Form\Type\RegisterForm;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use function Symfony\Component\String\u;

/**
 * @see AppControllerTest
 */
#[AsController]
#[Route(name: 'app_')]
final class AppController extends AbstractController
{
    public function __construct(
        private ParameterBagInterface $bag,
        private KernelInterface $kernel
    ) {

    }
    private function getImportMapData(): array
    {
        $dir = $this->bag->get('kernel.project_dir');
        return require($dir . '/importmap.php');
    }
    /**
     * Simple page with some dynamic content.
     */
    #[Route(path: '/', name: 'home')]
    public function home(): Response
    {
        $readme = file_get_contents(__DIR__.'/../../README.md');
        $importMapData = $this->getImportMapData();

        return $this->render('home.html.twig', compact('importMapData', 'readme'));
    }

    #[Route(path: '/container', name: 'container')]
    public function container(Request $request): Response
    {
        $data = $this->runCommand('debug:container');

        $readme = file_get_contents(__DIR__.'/../../README.md');
        $importMapData = $this->getImportMapData();
        $perPage = $request->get('perPage', 15);

        return $this->render('container.html.twig', compact('perPage', 'data'));
    }

    public function runCommand(string $command): array
    {
        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => $command,
            // (optional) define the value of command arguments
//            'fooArgument' => 'barValue',
            // (optional) pass options to the command
            '--format' => 'json',
            // (optional) pass options without value
//            '--baz' => true,
        ]);

        // You can use NullOutput() if you don't need the output
        $output = new BufferedOutput();
        $application->run($input, $output);

        // return the output, don't use if you used NullOutput()
        $content = $output->fetch();
//        if (!$content) dd($input);
        // hack that removes the comments
        $content = '{' . u($content)->after("]\n{")->toString();
        $content = u($content)->before('//')->toString();
//        dump($content);
        assert(json_validate($content), $content);
        $data = json_decode($content, true);
        return $data;
    }

    #[Route('/chart', name: 'chart')]
    public function chart(ChartBuilderInterface $chartBuilder): Response
    {
        $chart = $chartBuilder->createChart(Chart::TYPE_LINE);

        $chart->setData([
            'labels' => ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
            'datasets' => [
                [
                    'label' => 'My First dataset',
                    'backgroundColor' => 'rgb(255, 99, 132)',
                    'borderColor' => 'rgb(255, 99, 132)',
                    'data' => [0, 10, 5, 2, 20, 30, 45],
                ],
            ],
        ]);

        $chart->setOptions([
            'scales' => [
                'y' => [
                    'suggestedMin' => 0,
                    'suggestedMax' => 100,
                ],
            ],
        ]);

        // is this working?
        $chart->setOptions([
            'plugins' => [
                'zoom' => [
                    'zoom' => [
                        'wheel' => ['enabled' => true],
                        'pinch' => ['enabled' => true],
                        'mode' => 'xy',
                    ],
                ],
            ],
        ]);

        return $this->render('chart.html.twig', [
            'chart' => $chart,
        ]);
    }

    /**
     * Displays requires from the composer.json file.
     */
    #[Route(path: '/composer', name: 'composer')]
    public function composer(): Response
    {
        $composer = file_get_contents(__DIR__.'/../../composer.json');
        $composerData = json_decode($composer, true);


        return $this->render('composer.html.twig', compact('composerData'));
    }

    /**
     * A simple form.
     */
    #[Route(path: '/form', name: 'form')]
    public function form(Request $request): Response
    {
        $dto = new RegisterFormDto();
        $form = $this->createForm(RegisterForm::class, $dto)->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // just for the example, the DTO has already been updated
            $dto = $form->getData();
        }

        return $this->render('form.html.twig', compact('form', 'dto'));
    }
}
