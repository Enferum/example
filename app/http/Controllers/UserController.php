<?php
    
    namespace App\Http\Controllers\Backend;
    
    use App\Collections\UserCollection;
    use App\Http\Controllers\Controller;
    use App\Http\Requests\Backend\User\CreateRequest;
    use App\Http\Requests\Backend\User\UpdateRequest;
    use App\Models\User;
    use App\Notifications\SmsNotification;
    use App\Repositories\PageRepository;
    use App\Repositories\ServiceRepository;
    use App\Repositories\UserRepository;
    use Illuminate\Contracts\Foundation\Application;
    use Illuminate\Contracts\View\Factory;
    use Illuminate\Http\JsonResponse;
    use Illuminate\Http\RedirectResponse;
    use Illuminate\Routing\Redirector;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\View\View;
    
    /**
     * Class UserController
     *
     * @package App\Http\Controllers\Backend
     */
    class UserController extends Controller
    {
        
        /**
         * @var UserRepository
         */
        private $repository;
        /**
         * @var PageRepository
         */
        private $pageRepository;
        /**
         * @var ServiceRepository
         */
        private $serviceRepository;
        
        /**
         * UserController constructor.
         *
         * @param UserRepository    $repository
         * @param PageRepository    $pageRepository
         * @param ServiceRepository $serviceRepository
         */
        public function __construct(UserRepository $repository, PageRepository $pageRepository, ServiceRepository $serviceRepository)
        {
            $this->repository        = $repository;
            $this->pageRepository    = $pageRepository;
            $this->serviceRepository = $serviceRepository;
        }
        
        /**
         * Display a listing of the resource.
         *
         * @return Application|Factory|View
         */
        public function index()
        {
            $users = UserCollection::getUserListExcept([User::ROLE_ADMIN, User::ROLE_SUPERVISOR]);
            $roles = UserCollection::getRolesExcept([User::ROLE_ADMIN, User::ROLE_SUPERVISOR]);
            return view('backend.users.index', compact('users', 'roles'));
        }
        
        /**
         * Show the form for creating a new resource.
         *
         * @return Application|Factory|View
         */
        public function create()
        {
            return view('backend.users.create', [
                'user'  => new User(),
                'roles' => UserCollection::getRolesExcept([User::ROLE_ADMIN, User::ROLE_SUPERVISOR]),
            ]);
        }
        
        /**
         * Store a newly created resource in storage.
         *
         * @param CreateRequest $request
         * @return Application|RedirectResponse|Redirector
         */
        public function store(CreateRequest $request)
        {
            $user = $this->repository->create($request);
            
            $this->repository->storeAvatar();
            
            if ($user->isCelebrity()) {
                $this->repository->createStarPage();
            }
            
            $user->notify(new SmsNotification());
            
            return redirect()->route('backend.user.edit', $user)->with('success', 'Пользователь создан');
        }
        
        /**
         * Show the form for editing the specified resource.
         *
         * @param int $id
         * @return Application|Factory|View
         */
        public function edit(int $id)
        {
            return view('backend.users.edit', [
                'user'  => User::with(['page', 'page.tags', 'page.services'])->findOrFail($id),
                'roles' => UserCollection::getRolesExcept([User::ROLE_ADMIN, User::ROLE_SUPERVISOR]),
            ]);
        }
        
        /**
         * Update the specified resource in storage.
         *
         * @param UpdateRequest $request
         * @param int           $id
         * @return Application|RedirectResponse|Redirector
         */
        public function update(UpdateRequest $request, int $id)
        {
            $user = User::findOrFail($id);
            $this->repository->update();
            $this->repository->storeAvatar();
            if ($user->isCelebrity()) {
                $this->pageRepository->create($user);
                $this->pageRepository->storeBgImage();
                $this->repository->storeTags();
                $this->serviceRepository->store($user);
            }
            return redirect()
                ->route('backend.user.edit', $this->repository->get())
                ->with('success', 'Данные обновлены');
        }
        
        /**
         * Remove the specified resource from storage.
         *
         * @param int $id
         *
         * @return JsonResponse
         */
        public function destroy(int $id)
        {
            User::findOrFail($id)->delete($id);
            return response()->json([
                'success' => 'Пользователь удален!',
            ]);
        }
        
        public function myProfile()
        {
            return view('backend.users.myPage', ['user' => Auth::guard('admin')->user()]);
        }
    }