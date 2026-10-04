<?php

namespace App\Repositories\Language;

use App\Models\Backend\FlagIcon;
use App\Models\Backend\Language;
use App\Repositories\Language\LanguageInterface;
use App\Traits\ReturnFormatTrait;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class LanguageRepository implements LanguageInterface
{
    use ReturnFormatTrait;

    protected $model;

    public function __construct(Language $model)
    {
        $this->model = $model;
    }

    public function flags()
    {
        return FlagIcon::all();
    }

    public function all($status = null, int $paginate = null, string $orderBy = 'id', string $sortBy = 'desc')
    {
        $query = $this->model::query();

        if ($status != null) {
            $query->where('status', $status);
        }

        $query->orderBy($orderBy, $sortBy);

        return $paginate != null ? $query->paginate($paginate) : $query->get();
    }

    public function find($id)
    {
        return $this->model::findOrFail($id);
    }

    public function store($request)
    {
        try {

            DB::beginTransaction();

            $language                   = new Language();
            $language->name             = $request->name;
            $language->code             = $request->code;
            $language->icon_class       = $request->icon_class;
            $language->text_direction   = strtoupper($request->text_direction);
            $language->status           = $request->status;
            $language->save();

            $path     = base_path('lang/' . $request->code);

            if (!File::isDirectory($path)) {
                File::makeDirectory($path, 0777, true, true);
                File::copyDirectory(base_path('lang/en'), $path);
            }

            DB::commit();

            Cache::forget("defaultLanguage-{$language->code}");

            return $this->responseWithSuccess(___('alert.successfully_added'));
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    public function update($request)
    {
        try {
            DB::beginTransaction();

            $language                    = $this->model::find($request->id);
            if ($language->code  != $request->code) : //if not match old code and new code


                $old_directory    = base_path('lang/' . $language->code);

                if (File::isDirectory($old_directory)) :
                    File::deleteDirectory($old_directory);
                endif;

                $path             = base_path('lang/' . $request->code);

                if (!File::isDirectory($path)) :
                    File::makeDirectory($path, 0777, true, true);
                    File::copyDirectory(base_path('lang/en'), base_path('lang/' . $request->code));
                endif;
            elseif ($language->code  == $request->code) :
                $path             = base_path('lang/' . $request->code);

                if (!File::isDirectory($path)) :
                    File::makeDirectory($path, 0777, true, true);
                    File::copyDirectory(base_path('lang/en'), base_path('lang/' . $request->code));
                endif;

            endif;

            $language->name             = $request->name;
            $language->code             = $request->code;
            $language->icon_class       = $request->icon_class;
            $language->text_direction   = strtoupper($request->text_direction);
            $language->status           = $request->status;
            $language->save();

            DB::commit();

            Cache::forget("defaultLanguage-{$language->code}");

            return $this->responseWithSuccess(___('alert.successfully_updated'));
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    //language delete
    public function delete($id)
    {
        try {
            $lang         = $this->model::find($id);

            if ($lang->code == 'en') {
                return $this->responseWithError(___('alert.delete_not_allowed'));
            }

            $path         = base_path('/lang/' . $lang->code);
            if (File::exists($path)) :
                File::deleteDirectory($path);
            endif;

            $jsonPath     = base_path('/lang/' . $lang->code . '.json');
            if (File::exists($jsonPath)) :
                unlink($jsonPath);
            endif;

            Cache::forget("defaultLanguage-{$lang->code}");
            app()->setLocale(settings('language') ?: 'en');
            session()->put('locale', app()->getLocale());

            $lang->delete();

            return $this->responseWithSuccess(___('alert.successfully_deleted'));
        } catch (\Throwable $th) {

            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }


    //edit phrase data
    public function editPhrase($id)
    {
        try {
            $lang           = $this->model::find($id);
            $jsonString     = file_get_contents(base_path("lang/" . $lang->code . "/alert.json"));
            $data['terms']  = json_decode($jsonString, true);

            return $this->responseWithSuccess('', $data);
        } catch (\Throwable $th) {

            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    //update phrase data
    public function updatePhrase($request)
    {
        try {
            $filename   = base_path("lang/$request->code/$request->module.json");

            $jsonString     = file_get_contents($filename);
            $data           = json_decode($jsonString, true);

            foreach ($data as $key => $value) {
                $data[$key] = $request->phrases[$key] ?? $value;
            }

            $newJsonString = json_encode($data, JSON_UNESCAPED_UNICODE);
            file_put_contents($filename, stripslashes($newJsonString));

            return $this->responseWithSuccess(___('alert.successfully_updated'), status_code: 201);
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }
}
