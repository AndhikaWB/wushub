<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Peserta;
use App\Models\Lomba;
use App\Models\User;

class PesertaController extends Controller
{
    public $username;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->username = Auth::user()->username;
            return $next($request);
        });
    }

    public function index(Request $request) {
        $id_lomba = $request->route('id_lomba');
        $lomba = Lomba::all()->where('id', $id_lomba)->first();
        if (!$lomba) return redirect()->route('lomba');

        $daftar_peserta = Peserta::all()->where('id_lomba', $id_lomba);
        $username_peserta_terpakai = array();
        foreach ($daftar_peserta as $peserta) {
            if ($peserta->username) {
                array_push($username_peserta_terpakai, $peserta->username);
            }
        }
        
        in_array($this->username, $username_peserta_terpakai) ? $terdaftar = true : $terdaftar = false;

        $daftar_user = User::select('username', 'name')->whereNotIn('username', $username_peserta_terpakai)->get();
        $daftar_user = $daftar_user->map(function ($user) {
            return ['id' => $user->username, 'text' => $user->name . ' (' . $user->username . ')' ];
        })->toArray();

        $daftar_kategori = collect($lomba->kategori)->map(function ($kategori) {
            return ['id' => $kategori, 'text' => $kategori ];
        })->toArray();

        return view('peserta', [
            'lomba' => $lomba,
            'username' => $this->username,
            'daftar_user' => $daftar_user, // belum terdaftar lomba
            'daftar_peserta' => $daftar_peserta, // terdaftar lomba
            'daftar_kategori' => $daftar_kategori,
            'terdaftar' => $terdaftar
        ]);
    }

    public function createOrUpdate(Request $request) {
        // Shortcut ke variabel penting
        $id_lomba = $request->route('id_lomba');
        $username = $request->input('username');
        $old_username = $request->input('old_username');

        // Validasi form
        $request->validate([
            'username' => 'required|string',
            'kategori' => 'required|array',
            'status' => 'required|string',
        ]);

        // Perbarui atau buat model baru
        $peserta = Peserta::all()
            ->where('id_lomba', $id_lomba)
            ->where('username', '=', $username)
            ->first();
        if (!$peserta) $peserta = new Peserta();

        $peserta->id_lomba = $id_lomba;
        $peserta->username = $username;
        $peserta->kategori = $request->input('kategori');
        $peserta->status = $request->input('status');
        $peserta->save();

        // Hapus model lama
        if ($old_username && $old_username != $username) $peserta = Peserta::all()
            ->where('id_lomba', $id_lomba)
            ->where('username', '=', $old_username)
            ->first()->delete();

        return redirect()->route('peserta', $id_lomba);
    }

    public function delete(Request $request) {
        // Shortcut ke variabel penting
        $id_lomba = $request->route('id_lomba');
        $username = $request->input('username');

        // Validasi form
        $request->validate([
            'username' => 'required|string',
        ]);

        // Hapus model bila ditemukan
        $peserta = Peserta::all()
            ->where('id_lomba', $id_lomba)
            ->where('username', '=', $username)
            ->first();
        if ($peserta) $peserta->delete();

        return redirect()->route('peserta', $id_lomba);
    }
}
