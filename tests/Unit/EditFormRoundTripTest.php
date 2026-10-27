<?php

namespace Tests\Unit;

use App\User;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Opening an edit page and pressing Update without touching anything must not change the record.
 * The form is submitted exactly as a browser would: every field with its rendered value, only the
 * checked radios/checkboxes, and the selected (or first) option of each select.
 */
class EditFormRoundTripTest extends TestCase
{
    use DatabaseTransactions;

    #[DataProvider('forms')]
    public function test_saving_unchanged_edit_form_keeps_record($editUrl, $table, $field, $find)
    {
        $admin = $this->makeAdmin();
        $id = $this->findRecord($find, $table, $admin);

        foreach (['0', '1'] as $saved) {
            DB::table($table)->where('id', $id)->update([$field => $saved]);
            $before = (array) DB::table($table)->where('id', $id)->first();

            $url = sprintf($editUrl, $id);
            $_SERVER['REQUEST_URI'] = '/'.$url;
            [$action, $fields, $radioFields] = $this->browserSubmission($this->actingAs($admin)->get($url)->assertOk()->getContent(), $id);

            $response = $this->actingAs($admin)->post($action, $fields);
            $this->assertNull(session('errors'), "$url ($field=$saved): unchanged form failed validation: ".json_encode(session('errors') ? session('errors')->all() : null));
            $this->assertNull(session('fails'), "$url ($field=$saved): unchanged form failed: ".json_encode(session('fails')));
            $this->assertTrue($response->isRedirect(), "$url ($field=$saved): expected a redirect, got ".$response->getStatusCode());

            // every radio on the page that maps to a column must keep its saved value; other columns are
            // not compared, some forms already rewrite e.g. NULL notes as '' or drop unrendered permissions (pre-existing)
            $after = (array) DB::table($table)->where('id', $id)->first();
            $columns = array_values(array_intersect($radioFields, array_keys($before)));
            $this->assertContains($field, $columns);
            $this->assertSame(array_intersect_key($before, array_flip($columns)), array_intersect_key($after, array_flip($columns)), "$url ($field=$saved): saving the form unchanged changed a radio choice");
            session()->flush();
        }
    }

    public static function forms()
    {
        return [
            'agent'          => ['agents/%s/edit', 'users', 'active', 'agent'],
            'group'          => ['groups/%s/edit', 'groups', 'group_status', 'group'],
            'user status'    => ['user/%s/edit', 'users', 'active', 'user'],
            'user ban'       => ['user/%s/edit', 'users', 'ban', 'user'],
            'help topic'     => ['helptopic/%s/edit', 'help_topic', 'type', 'first'],
            'ban list'       => ['banlist/%s/edit', 'users', 'ban', 'user'],
            'kb article'     => ['article/%s/edit', 'kb_article', 'type', 'first'],
            'kb article vis' => ['article/%s/edit', 'kb_article', 'status', 'first'],
            'kb category'    => ['category/%s/edit', 'kb_category', 'status', 'first'],
            'kb page'        => ['page/%s/edit', 'kb_pages', 'status', 'page'],
            'kb page vis'    => ['page/%s/edit', 'kb_pages', 'visibility', 'page'],
            'close workflow' => ['close-workflow', 'workflow_close', 'send_email', 'first'],
            'rating'         => ['editratings/%s', 'ratings', 'allow_modification', 'first'],
        ];
    }

    private function findRecord($find, $table, $admin)
    {
        switch ($find) {
            case 'agent':
                // an agent other than the one logged in, so deactivating it does not log us out; the form requires a team
                $agent = $this->makeAdmin('agent');
                DB::table('team_assign_agent')->insert(['team_id' => DB::table('teams')->value('id'), 'agent_id' => $agent->id]);

                return $agent->id;
            case 'group':
                // a group without agents, a group with agents cannot be made inactive (by design)
                $group = (array) DB::table('groups')->first();
                unset($group['id']);
                $group['name'] = 'Group '.Str::random(6);

                return DB::table('groups')->insertGetId($group);
            case 'user':
                return DB::table('users')->where('role', '=', 'user')->where('email', '!=', '')->whereNotNull('email')->value('id');
            case 'page':
                $s = Str::random(6);

                return DB::table('kb_pages')->insertGetId(['name' => 'Page '.$s, 'status' => 1, 'visibility' => 1, 'slug' => 'page-'.strtolower($s), 'description' => 'Page body text', 'created_at' => now(), 'updated_at' => now()]);
            default:
                return DB::table($table)->value('id');
        }
    }

    /**
     * Returns [action path, fields, names of radio fields] of the edit form, the way a browser would submit it.
     */
    private function browserSubmission($html, $id)
    {
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new DOMXPath($dom);
        $form = null;
        foreach ($xpath->query('//form') as $candidate) {
            if ($xpath->query('.//input[@name="_method"]', $candidate)->length || $xpath->query('.//input[@type="radio"]', $candidate)->length) {
                $form = $candidate;
                break;
            }
        }
        $this->assertNotNull($form, 'edit form not found');

        $fields = [];
        $radioFields = [];
        // PHP's HTML parser nests a following form (e.g. the "add category" modal) inside this one,
        // a browser does not: only take fields whose nearest form is the edit form
        $own = function ($node) use ($xpath, $form) {
            return $xpath->query('ancestor::form[1]', $node)->item(0)->isSameNode($form);
        };
        $add = function ($name, $value) use (&$fields) {
            if (str_ends_with($name, '[]')) {
                $fields[substr($name, 0, -2)][] = $value;
            } else {
                parse_str(urlencode($name).'='.urlencode($value), $parsed);
                $fields = array_replace_recursive($fields, $parsed);
            }
        };
        foreach ($xpath->query('.//input[@name]', $form) as $input) {
            if (!$own($input)) {
                continue;
            }
            $type = strtolower($input->getAttribute('type') ?: 'text');
            if (in_array($type, ['submit', 'button', 'file', 'image', 'reset'])) {
                continue;
            }
            if ($type === 'radio') {
                $radioFields[] = $input->getAttribute('name');
            }
            if (in_array($type, ['radio', 'checkbox']) && !$input->hasAttribute('checked')) {
                continue;
            }
            if ($input->hasAttribute('disabled')) {
                continue;
            }
            $add($input->getAttribute('name'), $input->hasAttribute('value') ? $input->getAttribute('value') : ($type === 'checkbox' ? 'on' : ''));
        }
        foreach ($xpath->query('.//textarea[@name]', $form) as $textarea) {
            if (!$own($textarea)) {
                continue;
            }
            $add($textarea->getAttribute('name'), $textarea->textContent);
        }
        foreach ($xpath->query('.//select[@name]', $form) as $select) {
            if (!$own($select)) {
                continue;
            }
            $options = $xpath->query('.//option', $select);
            $selected = $xpath->query('.//option[@selected]', $select);
            if ($select->hasAttribute('multiple')) {
                foreach ($selected as $option) {
                    $add($select->getAttribute('name'), $option->hasAttribute('value') ? $option->getAttribute('value') : $option->textContent);
                }
            } elseif ($options->length) {
                $option = $selected->length ? $selected->item($selected->length - 1) : $options->item(0);
                $add($select->getAttribute('name'), $option->hasAttribute('value') ? $option->getAttribute('value') : trim($option->textContent));
            }
        }

        $action = parse_url($form->getAttribute('action'), PHP_URL_PATH);
        $action = preg_replace('#^.*?/(?=[^/]+/'.$id.'(/|$)|[^/]+$)#', '', $action) ?: $action;

        return [ltrim($action, '/'), $fields, array_unique($radioFields)];
    }

    private function makeAdmin($role = 'admin')
    {
        $s = Str::random(8);

        return User::create(['first_name' => 'T', 'last_name' => ucfirst($role), 'email' => $s.'@example.test', 'user_name' => 'a_'.$s,
            'password' => Hash::make(Str::random(10)), 'active' => 1, 'role' => $role, 'agent_tzone' => 81, 'primary_dpt' => 1, 'assign_group' => 1,
            'phone_number' => '', 'mobile' => null]);
    }
}
