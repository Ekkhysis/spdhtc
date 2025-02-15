<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gejala;
use App\Models\Penyakit;
use App\Models\Rule;
use Illuminate\Http\Request;

class RuleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = [
            'loginDuration' => $this->LoginDuration(),
            'rules' => $this->getRule(),
        ];
        return view('admin.rule.rule', $data);
    }

    public function getRule()
    {
        $rules = Rule::with(['penyakit' => function ($query) {
            $query->select('id', 'name');
        }, 'gejala' => function ($query) {
            $query->select('id', 'name');
        }])->get(['id', 'penyakit_id', 'gejala_id', 'updated_at'])->map(function ($rule) {
            $rule['updated_at'] = $rule['updated_at'];
            $rule['penyakit'] = $rule['penyakit']->toArray();
            $rule['gejala'] = $rule['gejala']->toArray();
            return [
                'id' => $rule['id'],
                'updated_at' => $rule['updated_at'],
                'penyakit' => $rule['penyakit'],
                'gejala' => $rule['gejala'],
            ];
        })->values()->toArray();

        return $rules;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $penyakit = Penyakit::select('id', 'name')->orderByDesc('updated_at')->get();
        $gejala = Gejala::select('id', 'name')->orderByDesc('updated_at')->get();

        $data = [
            'loginDuration' => $this->LoginDuration(),
            'penyakit' => $penyakit,
            'gejala' => $gejala,
        ];

        return view('admin.rule.tambah', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'penyakit' => 'required',
            'gejala' => 'required',
        ]);

        $gejala = $request->input('gejala');
        foreach ($gejala as $key => $value) {
            $gejala[$key] = (int) $value;
        }

        foreach ($gejala as $key => $value) {
            try {
                Rule::create([
                    'penyakit_id' => $request->input('penyakit'),
                    'gejala_id' => $value,
                ]);
            } catch (\Exception $e) {
                return redirect()->route('admin.rule')->with('error', 'Rule gagal ditambahkan');
            }
        }

        return redirect()->route('admin.rule')->with('success', 'Rule berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            $rule = Rule::with(['penyakit' => function ($query) {
                $query->select('id', 'name');
            }, 'gejala' => function ($query) {
                $query->select('id', 'name');
            }])->findOrFail($id, ['id', 'penyakit_id', 'gejala_id', 'updated_at'])->toArray();

            $penyakit = Penyakit::select('id', 'name')->orderByDesc('updated_at')->get();
            $gejala = Gejala::select('id', 'name')->orderByDesc('updated_at')->get();

            $data = [
                'loginDuration' => $this->LoginDuration(),
                'penyakit' => $penyakit,
                'gejala' => $gejala,
                'rule' => $rule,
            ];

            return view('admin.rule.edit', $data);
        } catch (\Exception $e) {
            return redirect()->route('admin.rule')->with('error', 'Rule tidak ditemukan');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $rule = Rule::findOrFail($id);
            $rule->penyakit_id = $request->input('penyakit');
            $rule->gejala_id = $request->input('gejala');
            $rule->save();
        } catch (\Exception $e) {
            return redirect()->route('admin.rule')->with('error', 'Rule gagal diubah');
        }

        return redirect()->route('admin.rule')->with('success', 'Rule berhasil diubah');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $rule = Rule::findOrFail($id);
            $rule->delete();
        } catch (\Exception $e) {
            return redirect()->route('admin.rule')->with('error', 'Rule gagal dihapus');
        }

        return redirect()->route('admin.rule')->with('success', 'Rule berhasil dihapus');
    }
}
