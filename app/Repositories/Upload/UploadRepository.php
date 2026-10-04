<?php

namespace App\Repositories\Upload;

use App\Models\Upload;
use App\Repositories\Upload\UploadInterface;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class UploadRepository implements UploadInterface
{
    /**
     * The only image formats this repository will decode/re-encode.
     *
     * uploadImage() used to build a function name straight from the uploaded
     * file's extension ('imagecreatefrom' . $fileType) and call it — so a
     * request with extension "shell_exec" or any other callable name would
     * have PHP invoke it. An explicit whitelist checked before any decoding
     * happens closes that off: anything not listed here is rejected with a
     * clear exception instead of being handed to a dynamically-built call.
     */
    private const ALLOWED_IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];

    public function uploadImage($image, $path, array $image_sizes = [], $old_upload_id = null)
    {
        if (blank($image)) {
            return $old_upload_id;
        }

        // delete old uploaded images
        if ($old_upload_id) {
            $this->deleteImage($old_upload_id, 'update');
        }

        $requestImage        = $image;
        $fileType            = strtolower($requestImage->getClientOriginalExtension());

        if ($fileType == 'jpg') {
            $fileType = 'jpeg';
        }

        // PDFs are moved as-is in imageSaveToStorage(), never decoded as an
        // image. Everything else must be on the whitelist above or the
        // upload is refused here, before a directory is even created.
        if ($fileType !== 'pdf' && ! in_array($fileType, self::ALLOWED_IMAGE_EXTENSIONS, true)) {
            throw new \InvalidArgumentException("Unsupported upload type: .{$fileType}");
        }

        $directory         = public_path("uploads/$path");

        // make directory if not exist
        if (!File::exists($directory)) {
            File::makeDirectory($directory);
        }

        // for original images
        $originalImageName = $this->imageName('original', $fileType);
        $originalImageUrl  = $directory . $originalImageName;
        $this->imageSaveToStorage($originalImageUrl, $requestImage, 'original', '', '', $fileType, $directory);

        $all_url = [];

        foreach ($image_sizes as $key => $image_size) {
            $imageName  = $this->imageName(++$key, 'webp');
            $imageUrl   = $directory . $imageName;
            $all_url[]  = $imageUrl;
            $this->imageSaveToStorage($imageUrl, $requestImage, '', $image_size[1], $image_size[0]);
        }

        $upload     = Upload::find($old_upload_id);
        if (!$upload) {
            $upload = new Upload();
        }

        $upload->original    = $this->publicRelativePath($originalImageUrl);
        $upload->image_one   = isset($all_url[0]) ? $this->publicRelativePath($all_url[0]) : null;
        $upload->image_two   = isset($all_url[1]) ? $this->publicRelativePath($all_url[1]) : null;
        $upload->image_three = isset($all_url[2]) ? $this->publicRelativePath($all_url[2]) : null;
        $upload->type        = $fileType == 'pdf' ? 'file' : 'image';
        $upload->save();
        return $upload->id;
    }

    /**
     * Turn an absolute upload path into one relative to public/.
     *
     * Everything that reads these values — logo(), getImage(), asset() — joins
     * them onto public_path(), so storing an absolute path yields
     * `public/home/user/.../public/uploads/...` and every image 404s.
     *
     * This previously did str_replace(public_path() . "\\uploads", ...), a
     * Windows separator baked into a double-quoted string. On Linux and macOS
     * the needle never matched, so the absolute path was stored verbatim and
     * every image uploaded from the admin panel came out broken. Separators are
     * normalised here so the result is identical on all three platforms.
     */
    private function publicRelativePath(string $absolutePath): string
    {
        $base = rtrim(str_replace('\\', '/', public_path()), '/') . '/';
        $path = str_replace('\\', '/', $absolutePath);

        if (str_starts_with($path, $base)) {
            $path = substr($path, strlen($base));
        }

        return ltrim($path, '/');
    }

    public function imageName($size, $fileType)
    {
        // Was `substr(0, 20) . $size . '.' . $fileType` — an integer (0) as
        // the haystack, coerced to "0", with a start offset (20) past its
        // length, so PHP silently returned "". Leftover from some earlier
        // version of this line; the name is just date + random + size + ext.
        $purpose = $size . '.' . $fileType;
        $purpose = str_replace(" ", "-", $purpose);
        $purpose = date('Y-m-d') . '-' . strtolower(Str::random(12)) . '-' . $purpose;

        return $purpose;
    }

    /**
     * intervention/image v3: ImageManager::read() decodes from the file's
     * actual bytes, not its extension, so the imagecreatefrom* lookup this
     * used to build is gone entirely — the whitelist in uploadImage() is what
     * decides whether a file is even handed to read(). scale() keeps the
     * aspect ratio for the general-purpose thumbnail sizes; cover() crops to
     * an exact square for the 80x80 icon size instead of squashing it (v2's
     * plain resize() there distorted non-square sources).
     */
    public function imageSaveToStorage($imageUrl, $requestImage, $original, $height = "", $width = "", $fileType = '', $directory = '')
    {
        if ($fileType == 'pdf') {
            $requestImage->move($directory, $imageUrl);

            return true;
        }

        $image = (new ImageManager(new Driver()))->read($requestImage->getRealPath());

        if ($original == 'original') {
            $image->save($imageUrl, quality: 90);
        } elseif ($height == 80 && $width == 80) {
            $image->cover((int) $width, (int) $height)->save($imageUrl, quality: 90);
        } else {
            $image->scale((int) $width, (int) $height)->save($imageUrl, quality: 90);
        }

        return true;
    }

    public function deleteImage($old_upload_id, $slug = "update")
    {
        $upload = Upload::where('id', $old_upload_id)->first();
        if ($upload) {
            if ($upload->original && File::exists(public_path($upload->original))) {
                unlink(public_path($upload->original));
            }
            if ($upload->image_one && File::exists(public_path($upload->image_one))) {
                unlink(public_path($upload->image_one));
            }
            if ($upload->image_two && File::exists(public_path($upload->image_two))) {
                unlink(public_path($upload->image_two));
            }
            if ($upload->image_three && File::exists(public_path($upload->image_three))) {
                unlink(public_path($upload->image_three));
            }

            if ($slug == "delete") {
                $upload->delete();
            }
        }

        return true;
    }

    function uploadSeederByPath(string $sourcePath = null, string $uploadDirectory = "assets", string $namePrefix = 'copy')
    {
        $uploadDirectory = "uploads/{$uploadDirectory}/";

        if (!file_exists(public_path($uploadDirectory))) {
            mkdir(public_path($uploadDirectory), 0755, true);
        }

        $basename           = pathinfo(public_path($sourcePath), PATHINFO_BASENAME);
        $name               = uniqid("{$namePrefix}_") . '_' . $basename;
        $destinationPath    = $uploadDirectory . $name;

        if (!copy(public_path($sourcePath), public_path($destinationPath))) {
            return null;
        }

        $upload              = new Upload();
        $upload->original    = $destinationPath;
        $upload->image_one   = $destinationPath;
        $upload->image_two   = $destinationPath;
        $upload->image_three = $destinationPath;

        $fileType            = pathinfo($destinationPath, PATHINFO_EXTENSION);
        $upload->type        = in_array($fileType, ['jpg', 'jpeg', 'png', 'gif']) ? 'image' : null;

        $upload->save();

        return $upload->id;
    }
}
