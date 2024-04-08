<?php

namespace Botble\RealEstate\Http\Controllers;

use Botble\Base\Events\BeforeEditContentEvent;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Forms\FormBuilder;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\RealEstate\Forms\PropertyForm;
use Botble\RealEstate\Http\Requests\PropertyRequest;
use Botble\RealEstate\Repositories\Interfaces\ProjectInterface;
use Botble\RealEstate\Repositories\Interfaces\FeatureInterface;
use Botble\RealEstate\Repositories\Interfaces\PropertyInterface;
use Botble\RealEstate\Services\SaveFacilitiesService;
use Botble\RealEstate\Tables\PropertyTable;
use Botble\RealEstate\Models\Account;
use Botble\RealEstate\Services\StorePropertyCategoryService;
use Exception;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RealEstateHelper;
use Throwable;
use Botble\RealEstate\Repositories\Interfaces\AccountInterface;
use Botble\Base\Supports\RepositoryHelper;

class PropertyController extends BaseController
{
    /**
     * @var PropertyInterface $propertyRepository
     */
    protected $propertyRepository;

    /**
     * @var ProjectInterface
     */
    protected $projectRepository;

    /**
     * @var FeatureInterface
     */
    protected $featureRepository;

    /**
     * PropertyController constructor.
     * @param PropertyInterface $propertyRepository
     * @param ProjectInterface $projectRepository
     * @param FeatureInterface $featureRepository
     */
    public function __construct(
        PropertyInterface $propertyRepository,
        ProjectInterface $projectRepository,
        FeatureInterface $featureRepository
    ) {
        $this->propertyRepository = $propertyRepository;
        $this->projectRepository = $projectRepository;
        $this->featureRepository = $featureRepository;
    }

    /**
     * @param PropertyTable $dataTable
     * @return JsonResponse|View
     * @throws Throwable
     */
    public function index(PropertyTable $dataTable, Request $request, $id=null)
    {
        page_title()->setTitle(trans('plugins/real-estate::property.name'));

        if (request('response') == 'api') {
            if($request->project == 'true')
            {

                $params = [
                    'paginate' => [
                        'per_page'      => $request->per_page ?: 12,
                        'current_paged' => (int)$request->input('page', 1),
                    ],
                    'order_by' => ['re_projects.created_at' => 'DESC'],
                    'with'     => RealEstateHelper::getProjectRelationsQuery(),
                ];

                return $this->projectRepository->getProjects(collect($request)->toArray(), $params);
            }

            if($id){
                return $this->propertyRepository->getPropertyBySlug($id);
            }

            $params = [
                'paginate' => [
                    'per_page'      => $request->per_page ?: 12,
                    'current_paged' => (int)$request->input('page', 1),
                ],
                'order_by' => ['re_projects.created_at' => 'DESC'],
                'with'     => RealEstateHelper::getProjectRelationsQuery(),
            ];

            if($request->order_by == 'recent')
            {
                $params = [
                    'paginate' => [
                        'per_page'      => $request->per_page ?: 12,
                        'current_paged' => (int)$request->input('page', 1),
                    ],
                    'order_by' => ['re_projects.created_at' => 'DESC'],
                    'with'     => RealEstateHelper::getProjectRelationsQuery(),
                    'limit' => 4
                ];
            }


            if($request->from == 'homepage'){
                $properties = $this->propertyRepository->getProperties(['premium' => '1'], [
                    'paginate' => [
                        'per_page'      => 6,
                        'current_paged' => (int)$request->input('page', 1),
                    ],
                    'order_by' => ['re_projects.created_at' => 'DESC'],
                    'with'     => RealEstateHelper::getProjectRelationsQuery(),
                ]);

                $featured = $this->propertyRepository->getProperties(['featured' => '1'], [
                    'paginate' => [
                        'per_page'      => 6,
                        'current_paged' => (int)$request->input('page', 1),
                    ],
                    'order_by' => ['re_projects.created_at' => 'DESC'],
                    'with'     => RealEstateHelper::getProjectRelationsQuery(),
                ]);

                $sale = $this->propertyRepository->getProperties(['type' => 'sale'], [
                    'paginate' => [
                        'per_page'      => 8,
                        'current_paged' => (int)$request->input('page', 1),
                    ],
                    'order_by' => ['re_projects.created_at' => 'DESC'],
                    'with'     => RealEstateHelper::getProjectRelationsQuery(),
                ]);

                $rent = $this->propertyRepository->getProperties(['type' => 'rent'], [
                    'paginate' => [
                        'per_page'      => 8,
                        'current_paged' => (int)$request->input('page', 1),
                    ],
                    'order_by' => ['re_projects.created_at' => 'DESC'],
                    'with'     => RealEstateHelper::getProjectRelationsQuery(),
                ]);

                $recent = $this->propertyRepository->getProperties([], [
                    'paginate' => [
                        'per_page'      => 4,
                        'current_paged' => (int)$request->input('page', 1),
                    ],
                    'order_by' => ['re_projects.created_at' => 'DESC'],
                    'with'     => RealEstateHelper::getProjectRelationsQuery(),
                    'limit'    => 4
                ]);

                return compact('properties', 'featured', 'sale', 'rent', 'recent');
            }


            $query = collect($request->all())->except('response')->toArray();
            return $this->propertyRepository->getProperties($query, [], $request->per_page ?? 6);
        }

        return $dataTable->renderTable();
    }

    /**
     * @param FormBuilder $formBuilder
     * @return string
     */
    public function create(FormBuilder $formBuilder)
    {
        page_title()->setTitle(trans('plugins/real-estate::property.create'));
        return $formBuilder->create(PropertyForm::class)->renderForm();
    }


    /**
     * @param PropertyRequest $request
     * @param BaseHttpResponse $response
     * @param StorePropertyCategoryService $propertyCategoryService,
     * @param SaveFacilitiesService $saveFacilitiesService
     * @return BaseHttpResponse
     * @throws FileNotFoundException
     */
    public function store(
        PropertyRequest $request,
        BaseHttpResponse $response,
        StorePropertyCategoryService $propertyCategoryService,
        SaveFacilitiesService $saveFacilitiesService
    ) {

        $request->merge([
            'expire_date' => now()->addDays(RealEstateHelper::propertyExpiredDays()),
            'images'      => json_encode(array_filter($request->input('images', []))),
            'author_type' => Account::class,
        ]);

        $property = $this->propertyRepository->getModel();
        $property = $property->fill($request->input());

        if($request->wantsJson()){
            $property->author_id = auth()->id();
        }

        $property->moderation_status = $request->wantsJson() ? 'pending' : $request->input('moderation_status');
        $property->never_expired = $request->input('never_expired');
        $property->save();


        event(new CreatedContentEvent(PROPERTY_MODULE_SCREEN_NAME, $request, $property));

        if ($property) {
            $property->features()->sync($request->input('features', []));

            $saveFacilitiesService->execute($property, $request->input('facilities', []));

            $propertyCategoryService->execute($request, $property);
        }

        if($request->wantsJson()){
            return $response
            ->setData($property)
            ->setMessage(trans('core/base::notices.create_success_message'));;
        }

        return $response
            ->setPreviousUrl(route('property.index'))
            ->setNextUrl(route('property.edit', $property->id))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    /**
     * @param int $id
     * @param Request $request
     * @param FormBuilder $formBuilder
     * @return string
     */
    public function edit($id, Request $request, FormBuilder $formBuilder)
    {
        $property = $this->propertyRepository->findOrFail($id, ['features', 'author']);
        page_title()->setTitle(trans('plugins/real-estate::property.edit') . ' "' . $property->name . '"');

        event(new BeforeEditContentEvent($request, $property));

        return $formBuilder->create(PropertyForm::class, ['model' => $property])->renderForm();
    }

    /**
     * @param int $id
     * @param PropertyRequest $request
     * @param BaseHttpResponse $response
     * @param StorePropertyCategoryService $propertyCategoryService
     * @param SaveFacilitiesService $facilitiesService
     * @return BaseHttpResponse
     * @throws FileNotFoundException
     */
    public function update(
        $id,
        PropertyRequest $request,
        BaseHttpResponse $response,
        StorePropertyCategoryService $propertyCategoryService,
        SaveFacilitiesService $saveFacilitiesService
    ) {
        $property = $this->propertyRepository->findOrFail($id);

        $property->fill($request->except(['expire_date']));

        $property->author_type = Account::class;

        if($request->wantsJson()){
            $property->author_id = auth()->id();
        }

        $property->images = json_encode(array_filter($request->input('images', [])));
        $property->moderation_status = $request->wantsJson() ? $property->moderation_status : $request->input('moderation_status');
        $property->never_expired = $request->input('never_expired');

        $this->propertyRepository->createOrUpdate($property);

        event(new UpdatedContentEvent(PROPERTY_MODULE_SCREEN_NAME, $request, $property));

        $property->features()->sync($request->input('features', []));

        $saveFacilitiesService->execute($property, $request->input('facilities', []));

        $propertyCategoryService->execute($request, $property);

        if($request->wantsJson()){
            return $response
            ->setData($property)
            ->setMessage('Updated Successfully');
        }

        return $response
            ->setPreviousUrl(route('property.index'))
            ->setNextUrl(route('property.edit', $property->id))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    /**
     * @param int $id
     * @param Request $request
     * @param BaseHttpResponse $response
     * @return BaseHttpResponse
     */
    public function destroy($id, Request $request, BaseHttpResponse $response)
    {
        try {
            $property = $this->propertyRepository->findOrFail($id);
            $property->features()->detach();
            $this->propertyRepository->delete($property);

            event(new DeletedContentEvent(PROPERTY_MODULE_SCREEN_NAME, $request, $property));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage(trans('core/base::notices.cannot_delete'));
        }
    }

    /**
     * @param Request $request
     * @param BaseHttpResponse $response
     * @return BaseHttpResponse
     * @throws Exception
     */
    public function deletes(Request $request, BaseHttpResponse $response)
    {
        $ids = $request->input('ids');
        if (empty($ids)) {
            return $response
                ->setError()
                ->setMessage(trans('core/base::notices.no_select'));
        }

        foreach ($ids as $id) {
            $property = $this->propertyRepository->findOrFail($id);
            $property->features()->detach();
            $this->propertyRepository->delete($property);

            event(new DeletedContentEvent(PROPERTY_MODULE_SCREEN_NAME, $request, $property));
        }

        return $response->setMessage(trans('core/base::notices.delete_success_message'));
    }

    public function getAgents(
        Request $request,
        BaseHttpResponse $response,
        AccountInterface $accountRepository
    ) {

        $accounts = $accountRepository->advancedGet([
            'condition' => [
                're_accounts.is_featured' => true,
            ],
            'order_by'  => [
                're_accounts.id' => 'DESC',
            ],
            'take'      => $request->input('limit') ? (int)$request->input('limit') : 4,
            'withCount' => [
                'properties' => function ($query) {
                    return RepositoryHelper::applyBeforeExecuteQuery($query, $query->getModel());
                },
            ],
        ]);

        return  $accounts;
    }

    public function getCities()
    {
        return \DB::table('cities')->get();
    }

    public function getFacilities()
    {
        return \DB::table('re_facilities')
        ->where('status', 'published')
        ->get();
    }

    public function getFeatures()
    {
        return \DB::table('re_features')
        ->where('status', 'published')
        ->get();
    }


}
