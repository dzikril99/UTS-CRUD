<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class PostController extends Controller
{
    //membuat method index
    public function index()
    {
        $posts = post::latest()->when(request()->search, function ($posts) {
            $posts = $posts->where('nim', 'like', '%' . request()->search . '%');
        })->paginate(5);

        return view('post.index', compact('posts'));
    }

    public function create()
    {
        return view('post.create');
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'nim'   =>  'required',
            'nama_mhs'  =>  'required',
            'nhp'   =>  'required',
            'alamat'    =>  'required',
            'image'      => 'required|image|mimes:png,jpg,jpeg'
        ]);

        //upload image

        $image = $request->file('image');
        $image->storeAs('public/posts', $image->hashName());

        $post = post::create([
            'image'     =>  $image->hashName(),
            'nim'   =>  $request->nim,
            'nama_mhs'      =>  $request->nama_mhs,
            'nhp'   =>  $request->nhp,
            'alamat'    =>  $request->alamat,
        ]);

        if ($post) {
            return redirect()->route('post.index')
            ->with(['success'   =>  'Data Berhasil Disimpan!']);
        }
        else {
            return redirect()->route('post.index')
            ->with(['eror'  =>  'Data Gagal Disimpan!']);
        }
    }


    public function edit(Post $post)
    {
        return view('post.edit', compact('post'));
    }

    public function update(Request $request, Post $post)
    {
        $this->validate($request, [
            'nim'   =>  'required',
            'nama_mhs'  =>  'required',
            'nhp'   =>  'required',
            'alamat'    =>  'required',
            'image'      => 'required|image|mimes:png,jpg,jpeg'
        ]);
        
        //get data post by ID
        $post = Post::findOrFail($post->id);

        if($request->file('image') == "") {

            $post->update([
                'nim'   =>  $request->nim,
                'nama_mhs'      =>  $request->nama_mhs,
                'nhp'   =>  $request->nhp,
                'alamat'    =>  $request->alamat
            ]);
        } else {
            //hapus old image
            Storage::disk('local')->delete('public/posts/' . $post->image);

            //upload new image
            $image = $request->file('image');
            $image->storeAs('public/posts', $image->hashName());

            $post->update([
                'nim'   =>  $request->nim,
                'nama_mhs'      =>  $request->nama_mhs,
                'nhp'   =>  $request->nhp,
                'alamat'    =>  $request->alamat,
                'image'         =>  $image->hashName()
            ]);
        }

    if ($post) {
        return redirect()->route('post.index')
        ->with(['success'    =>  'Data Berhasil Diupdate!']);
    } else {
        return redirect()->route('post.index')
        ->with(['error' =>  'Data Gagal Diupdate!']);
        }
    }

    public function destroy($id)
    {
        $post = Post::findorFail($id);
        Storage::disk('local')->delete('public/posts/' . $post->image);
        $post->delete();

        if ($post) {
            return redirect()->route('post.index')
            ->with(['success'   =>  'Data Berhasil Dihapus!']);
        } else {
            return redirect()->route('post.index')
            ->with(['error' =>  'Data Gagal Dihapus!']);
        }
    }
}
