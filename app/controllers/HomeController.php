<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/Service.php';
require_once __DIR__ . '/../models/Location.php';
require_once __DIR__ . '/../models/ProviderProfile.php';
require_once __DIR__ . '/../models/Review.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/ServiceRequest.php';

class HomeController extends Controller
{
    public function index()
    {
        $categories = (new Category())->active();
        $topProviders = (new ProviderProfile())->search(['available_only' => true]);
        // Featured = highest rated among providers who actually have reviews, falling back
        // to any available provider so the section is never empty on a fresh install.
        usort($topProviders, fn($a, $b) => ($b['rating_count'] <=> $a['rating_count']) ?: ($b['avg_rating'] <=> $a['avg_rating']));
        $topProviders = array_slice($topProviders, 0, 6);

        // Real counts pulled from the database — never hardcoded marketing numbers.
        $stats = [
            'providers' => (new User())->countByRole('provider'),
            'services'  => (new Service())->count(['is_active' => 1]),
            'requests'  => (new ServiceRequest())->count(),
            'locations' => (new Location())->count(['is_active' => 1]),
        ];

        $this->view('home/index', [
            'title' => __('home'), 'categories' => $categories,
            'topProviders' => $topProviders, 'stats' => $stats,
        ]);
    }

    public function services()
    {
        $keyword = $this->input('keyword') ?: $this->input('q');
        $services = (new Service())->search($keyword);
        $this->view('home/services', ['title' => __('services'), 'services' => $services, 'keyword' => $keyword]);
    }

    public function providers()
    {
        $filters = [
            'category_id'    => $this->input('category_id') ?: null,
            'service_id'     => $this->input('service_id') ?: null,
            'location_id'    => $this->input('location_id') ?: null,
            'min_rating'     => $this->input('min_rating') ?: null,
            'verified_only'  => $this->input('verified_only') ?: null,
            'available_only' => $this->input('available_only') ?: null,
            // Accept both 'keyword' (the filter form) and 'q' (the homepage
            // hero search box) so a search from either place actually filters.
            'keyword'        => $this->input('keyword') ?: ($this->input('q') ?: null),
        ];
        $providers = (new ProviderProfile())->search($filters);

        // If a keyword search returned nothing, suggest the closest category
        // instead of leaving the visitor with a dead end.
        $suggestedCategories = [];
        if (empty($providers) && !empty($filters['keyword'])) {
            $suggestedCategories = (new Category())->active();
        }

        $categories = (new Category())->active();
        $locations = (new Location())->active();

        $this->view('home/providers', [
            'title' => __('providers'), 'providers' => $providers,
            'categories' => $categories, 'locations' => $locations, 'filters' => $filters,
            'suggestedCategories' => $suggestedCategories,
        ]);
    }

    public function providerProfile($id)
    {
        $providerModel = new ProviderProfile();
        $provider = $providerModel->fullProfile((int)$id);
        if (!$provider) { http_response_code(404); require __DIR__ . '/../views/errors/404.php'; exit; }

        $services = $providerModel->servicesFor((int)$id);
        $locations = $providerModel->locationsFor((int)$id);
        $portfolio = $providerModel->portfolioFor((int)$id);
        $availability = $providerModel->availabilityFor((int)$id);
        $reviews = (new Review())->forProvider((int)$id);

        $this->view('home/provider_profile', compact('provider', 'services', 'locations', 'portfolio', 'availability', 'reviews') + ['title' => $provider['name']]);
    }
}
