<?php

namespace App\Http\Requests\Menu;

use App\Rules\ValidMenuParent;
use Illuminate\Validation\Rule;
use App\Repositories\Menu\MenuRepository;
use Illuminate\Foundation\Http\FormRequest;

class StoreMenuRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'parent_id'  => ['nullable', 'exists:menus,id', new ValidMenuParent()],
            'title'      => ['required', 'string', 'max:150'],
            'url'        => ['nullable', 'string', 'max:200'],
            'icon'       => ['nullable', 'string', 'max:60'],
            'target'     => ['nullable', Rule::in(['_blank'])],
            // POSITIONS is key => label, so validate against the keys.
            'position'   => ['required', Rule::in(array_keys(MenuRepository::POSITIONS))],
            'sort_order' => ['required', 'integer', 'min:0'],
            'status'     => ['required', Rule::in(MenuRepository::STATUSES)],
        ];
    }
}
