<?php

namespace App\Http\Controllers\Instructor;

use App\Models\Level;
use App\Models\Price;
use App\Models\Course;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use RealRashid\SweetAlert\Facades\Alert;

class CourseController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:Leer Cursos')->only('index');
        $this->middleware('can:Crear Cursos')->only('create', 'store');
        $this->middleware('can:Actualizar Cursos')->only('edit', 'update', 'goals');
        $this->middleware('can:Eliminar Cursos')->only('destroy');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('instructor.courses.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = Category::pluck('name', 'id');
        $levels = Level::pluck('name', 'id');
        $prices = Price::pluck('name', 'id');

        return view('instructor.courses.create', compact('categories', 'levels', 'prices'));
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
            'title' => 'required',
            'slug' => 'required|unique:courses',
            'subtitle' => 'required',
            'description' => 'required',
            'category_id' => 'required',
            'level_id' => 'required',
            'price_id' => 'required',
            'file' => 'image',
        ]);

        $course = Course::create($request->all());

        if ($request->hasFile('file')) {
            $image = $request->file('file');
            $url = Storage::put('cursos', $image);

            $course->image()->create([
                'url' => $url,
            ]);
        }

        Alert::toast('El curso fue añadido exitosamente', 'success');
        return redirect()->route('instructor.courses.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Course $course)
    {
        return view('instructor.courses.show', compact('course'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  Course $course
     * @return \Illuminate\Http\Response
     */
    public function edit(Course $course)
    {
        $this->authorize('dicatated', $course);

        $categories = Category::pluck('name', 'id');
        $levels = Level::pluck('name', 'id');
        $prices = Price::pluck('name', 'id');

        return view('instructor.courses.edit', compact('course', 'categories', 'levels', 'prices'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  Course $course
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Course $course)
    {
        $this->authorize('dicatated', $course);
        $request->validate([
            'title' => 'required',
            //'slug' => 'required|unique:courses, slug,' . $course->id,
            'subtitle' => 'required',
            'description' => 'required',
            'category_id' => 'required',
            'level_id' => 'required',
            'price_id' => 'required',
            'file' => 'image',
        ]);

        $course->update($request->all());

        if ($request->hasFile('file')) {
            $image = $request->file('file');
            $url = Storage::put('cursos', $image);

            if ($course->image) {
                Storage::delete($course->image->url);
                $course->image->update([
                    'url' => $url,
                ]);
            } else {
                $course->image()->create([
                    'url' => $url,
                ]);
            }
        }

        Alert::toast('El curso fue actualizado exitosamente', 'success');
        return redirect()->route('instructor.courses.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  Course $course
     * @return \Illuminate\Http\Response
     */
    public function destroy(Course $course)
    {
        //
    }

    public function goals(Course $course)
    {
        return view('instructor.courses.goals', compact('course'));
    }

    public function status(Course $course){
        $course->status = 2;
        $course->save();

        return back();
    }
}
