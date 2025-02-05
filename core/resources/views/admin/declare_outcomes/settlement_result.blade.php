@extends('admin.layouts.app')

@section('panel')

    <div class="row">

        <div class="col-md-7">
            <div class="row">
                <div class="col-lg-12">

                    <button id="download-pdf" class="btn btn-primary">Download PDF</button>

                    <div class="card b-radius--10" id="pdf-content">
                        <table class="table table-bordered"  id="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Market ID</th>
                                    <th>Odd Name</th>
                                    <th>Outcome</th>
                                    <th>Settled Time</th>
                                </tr>
                            </thead>
                            <tbody>
                               
                            </tbody>
                        </table>
                        <div id="pagination">
                            <button id="prev" disabled>Previous</button>
                            <button id="next">Next</button>
                        </div>
                    </div>

                </div>
            </div>
        </div>



    </div>




    <x-confirmation-modal/>

@endsection



@push('breadcrumb-plugins')

    <x-search-form/>

@endpush



@push('style')



@endpush



@push('script')
    <!-- Add this in your Blade template's <head> section or before the closing </body> tag -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js"></script>
    <script>
        document.getElementById('download-pdf').addEventListener('click', function () {
            // Select the content to capture
            const content = document.getElementById('pdf-content');

            // Use html2pdf to generate the PDF and trigger download
            html2pdf(content, {
                margin: 0.5,
                filename: 'settlements.pdf',
                image: {type: 'jpeg', quality: 0.98},
                html2canvas: {scale: 2},
                jsPDF: {unit: 'in', format: 'a4', orientation: 'portrait'}
            });
        });
    </script>
    
    <script>
    const data = @json($data); // Pass the data as JSON to JavaScript
    const rowsPerPage = 100;
    let currentPage = 1;

    function renderTable(page) {
        const tbody = document.querySelector('#data-table tbody');
        tbody.innerHTML = ''; // Clear existing rows

        const start = (page - 1) * rowsPerPage;
        const end = page * rowsPerPage;
        const pageData = data.slice(start, end);

        pageData.forEach(item => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${item.id}</td>
                <td>${item.marketId}</td>
                <td>${item.oddName}</td>
                <td>${item.outcome}</td>
                <td>${item.settledTime}</td>
            `;
            tbody.appendChild(row);
        });

        document.getElementById('prev').disabled = page === 1;
        document.getElementById('next').disabled = end >= data.length;
    }

    document.getElementById('prev').addEventListener('click', () => {
        if (currentPage > 1) {
            currentPage--;
            renderTable(currentPage);
        }
    });

    document.getElementById('next').addEventListener('click', () => {
        if (currentPage * rowsPerPage < data.length) {
            currentPage++;
            renderTable(currentPage);
        }
    });

    renderTable(currentPage); // Initial render
</script>

@endpush