<?php namespace Datlv\File;

use DataTables;
use Illuminate\Http\Request;
use Datlv\Kit\Extensions\BackendController as BaseController;
use Datlv\Kit\Extensions\DatatableBuilder as Builder;
use Datlv\Kit\Traits\Controller\CheckDatatablesInput;
use Datlv\Kit\Traits\Controller\QuickUpdateActions;

/**
 * Class BackendController
 *
 * @package Datlv\File
 */
class BackendController extends BaseController
{
    use QuickUpdateActions;
    use CheckDatatablesInput;

    /**
     * @param \Datlv\Kit\Extensions\DatatableBuilder $builder
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Builder $builder)
    {
        $this->buildHeading(trans('file::common.manage_title'), 'fa-newspaper-o', ['#' => trans('file::common.file')]);

        $builder->ajax(route('backend.file.data'));
        $html = $builder->columns([
            ['data' => 'id', 'name' => 'id', 'title' => 'ID', 'class' => 'min-width text-center'],
            [
                'data' => 'icon',
                'name' => 'icon',
                'title' => '',
                'class' => 'min-width',
                'orderable' => false,
                'searchable' => false,
            ],
            [
                'data' => 'title',
                'name' => 'title',
                'title' => trans('file::common.title'),
                'class' => 'file-title',
            ],
        ])->addAction([
            'data' => 'actions',
            'name' => 'actions',
            'title' => trans('common.actions'),
            'class' => 'min-width',
        ]);

        return view('file::backend.index', compact('html'));
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function data(Request $request)
    {
        $this->filterDatatablesParametersOrAbort($request);
        File::emptyTmp();
        $query = File::where('tmp', 0);
        if ($request->has('filter_form')) {
            $query =
                $query->searchWhereBetween('files.created_at', 'mb_date_vn2mysql')->searchWhereBetween('files.updated_at', 'mb_date_vn2mysql');
        }

        return DataTables::of($query)->setTransformer(new FileTransformer())->make(true);
    }

    /**
     * Todo: File validation (mime, size...)
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $file = new File();
        $error = $file->fillRequest($request);

        return response()->json([
            'type' => $error ? 'error' : 'success',
            'content' => $error ?: trans('file::common.upload_success'),
            'file' => $error ? null : $file->forReturn(),
        ]);
    }

    /**
     * @param \Datlv\File\File $file
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show(File $file)
    {
        return view('file::backend.show', compact('file'));
    }

    /**
     * @param \Datlv\File\File $file
     */
    public function preview(File $file)
    {
        $file->response();
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \Datlv\File\File $file
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, File $file)
    {
        $error = $file->fillFile($request);
        if (is_null($error)) {
            $file->save();
        }

        return response()->json([
            'type' => $error ? 'error' : 'success',
            'content' => $error ?: trans('file::common.replace_success'),
            'file' => $error ? null : $file->forReturn(),
        ]);
    }

    /**
     * @param \Datlv\File\File $file
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(File $file)
    {
        $file->delete();

        return response()->json([
            'type' => 'success',
            'content' => trans('common.delete_object_success', ['name' => trans('file::common.file')]),
        ]);
    }

    /**
     * Các attributes cho phép quick-update
     *
     * @return array
     */
    protected function quickUpdateAttributes()
    {
        return [
            'title' => [
                'rules' => 'required|max:255',
                'label' => trans('file::common.title'),
            ],
        ];
    }
}