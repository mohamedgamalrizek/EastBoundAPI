<?php
namespace Tests\Feature;
use App\Http\Controllers\Api\WebsiteContentController;
use App\Models\{Package,WebsiteDestination,WebsiteCity};
use App\Services\Website\WebsitePresenter;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;
class WebsiteContentTest extends TestCase
{
    protected function setUp(): void {
        parent::setUp();
        config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:','activitylog.enabled'=>false]);
        Schema::create('packages',function(Blueprint $t){$t->id();$t->string('title');$t->string('status');$t->decimal('price');$t->integer('duration_days');$t->integer('duration_nights');$t->string('image')->nullable();});
        (require database_path('migrations/2026_11_15_000001_add_website_destinations_and_tour_content.php'))->up();
        Schema::create('reviews',function(Blueprint $t){$t->id();$t->integer('package_id');$t->integer('rating');$t->string('status');$t->timestamps();});
        Schema::create('package_itineraries',function(Blueprint $t){$t->id();$t->integer('package_id');$t->integer('day_number');$t->string('title');$t->text('description');});
        WebsiteDestination::create(['id'=>1,'name'=>'Egypt','slug'=>'egypt','active'=>true]);
        WebsiteDestination::create(['id'=>2,'name'=>'Hidden','slug'=>'hidden','active'=>false]);
        foreach ([[1,1,'active',1],[2,1,'active',0],[3,2,'active',1],[4,1,'inactive',1]] as [$id,$country,$status,$published]) {
            DB::table('packages')->insert(['id'=>$id,'title'=>'Tour '.$id,'status'=>$status,'price'=>98765,'duration_days'=>5,'duration_nights'=>4,
                'website_slug'=>'tour-'.$id,'website_destination_id'=>$country,'website_published'=>$published]);
        }
    }
    public function test_public_catalogue_excludes_unpublished_and_hidden_countries_and_prices(): void {
        $this->assertSame([1],Package::forWebsite()->pluck('id')->all());
        $response=app(WebsiteContentController::class)->tours(Request::create('/','GET',['per_page'=>10]));
        $data=$response->getData(true)['data'];
        $this->assertSame(1,$data['meta']['total']);
        $this->assertNull($data['tours'][0]['price']);
        $this->assertSame('enquiry',$data['tours'][0]['bookingMode']);
        $this->assertStringNotContainsString('98765',$response->getContent());
    }
    public function test_city_route_is_scoped_to_its_country(): void {
        WebsiteCity::create(['destination_id'=>2,'name'=>'Hidden city','slug'=>'city','active'=>true]);
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        app(WebsiteContentController::class)->city('egypt','city');
    }
    public function test_only_safe_navigation_urls_are_exposed(): void {
        $presenter=new WebsitePresenter;
        foreach (['javascript:alert(1)','data:text/html,x','//external.test'] as $url) $this->assertNull($presenter->link($url));
        $this->assertSame('/tours',$presenter->link('/tours'));
    }
}
