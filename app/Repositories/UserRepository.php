<?php
    
    namespace App\Repositories;
    
    use App\Models\StarPage;
    use App\Models\Tag;
    use App\Models\User;
    use Illuminate\Contracts\Filesystem\Filesystem;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;
    use Intervention\Image\Facades\Image;
    
    class UserRepository
    {
        /**
         * @var
         */
        protected $user;
        /**
         * @var Request
         */
        private $request;
        /**
         * @var Filesystem
         */
        private $storage;
        
        /**
         * UserRepository constructor.
         *
         * @param Request $request
         */
        public function __construct(Request $request)
        {
            $this->request = $request;
            $this->storage = Storage::disk('avatar');
        }
        
        /**
         * @param $request
         * @return User
         */
        public function create($request)
        {
            $user = new User();
            
            $user->fill($request->validated());
            
            $user->setRememberToken(Str::random(60));
            $user->password = bcrypt($request->input('password'));
            $user->role = (int)$this->request->role;
            $user->sex = (int)$this->request->sex;
            
            
            $user->save();
            
            $this->user = $user;
            
            return $user;
        }
        
        /**
         * Update user avatar
         */
        public function storeAvatar()
        {
            if ($this->request->file('photo')) {
                $this->storage->delete($this->user->photo);
                $avatar   = $this->request->file('photo');
                $filename = Str::random(32) . '.' . $avatar->getClientOriginalExtension();
                $resize   = Image::make($avatar)->resize(320, 240);
                
                $this->storage->put($filename, $resize->stream());
                
                $this->user->update([
                    'photo' => $filename,
                ]);
            }
        }
        
        /**
         *  Update user data
         */
        public function update()
        {
            $user = User::findOrFail($this->request->route()->parameters['user']);
            $data = $this->request->except('photo');
            if ($user->isCelebrity()) {
                $data = $this->request->except('phone', 'email');
            }
            $user->fill($data);
            
            if ($this->request->password && $this->request->password != '') {
                $user->password = bcrypt($this->request->password);
            }
            $user->update($data);
            $this->user = $user;
        }
        
        /**
         * @return mixed
         */
        public function get()
        {
            return $this->user;
        }
        
        /**
         * Overwrite all celebrity page tags
         */
        public function storeTags()
        {
            $this->user->page->tags()->delete();
            collect($this->request->tags)->each(function ($tag) {
                if (trim($tag) !== '') {
                    Tag::create([
                        'name'         => $tag,
                        'star_page_id' => $this->user->page->id,
                    ]);
                }
            });
        }
    
        /**
         * Create Star page for celebrity
         */
        public function createStarPage()
        {
            $starPage          = new StarPage();
            $starPage->user_id = $this->user->id;
            $starPage->url = $this->user->name_nick;
            $starPage->save();
        }
    }