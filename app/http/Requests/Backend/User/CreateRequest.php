<?php
    
    namespace App\Http\Requests\Backend\User;
    
    use App\Models\User;
    use Illuminate\Foundation\Http\FormRequest;
    use Illuminate\Validation\Rule;
    
    class CreateRequest extends FormRequest
    {
        /**
         * Get the validation rules that apply to the request.
         *
         * @return array
         */
        public function rules()
        {
            return [
                'name_last' => [
                    'required',
                    'string',
                    'max:255'
                ],
                'name_first' => [
                    'required',
                    'string',
                    'max:255'
                ],
                'name_middle' => [
                    'min:0',
                    'max:255'
                ],
                'name_nick' => [
                    'string',
                    'max:255'
                ],
                'phone' => [
                    'required',
                    'unique:users,phone',
                    'regex:/' . config('app.phone_chars') . '/'
                ],
                'photo' => [
                    'file',
                    'max:5120',
                    'mimes:jpeg,png'
                ],
                'email' => [
                    'required',
                    'email',
                    'unique:users,email'
                ],
                'role' => [
                    'required',
                    Rule::in([User::ROLE_CLIENT,User::ROLE_CELEBRITY,User::ROLE_SUPERVISOR]),
                ],
                'sex' => [
                    Rule::in([User::SEX_MALE,User::SEX_FEMALE,User::SEX_ANOTHER,User::SEX_NOT_SET]),
                ]
            ];
        }
        
        public function messages()
        {
            return [
                'email.unique' => 'Пользователь с данной почтой зарегистрирван в системе.'
            ];
        }
        
        public function attributes()
        {
            return [
                'name_last'  => 'Фамилия',
                'name_first'  => 'Имя',
                'name_nick'  => 'Псевдоним',
            ];
        }
    }