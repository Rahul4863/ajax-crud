<!DOCTYPE html>
<html lang="en">
<head>
  <title>Bootstrap 5 Example</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<h1></h1>

<div class="container mt-5">
  <div class="card">
    <div class="card-header">All Data
      <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#myModal">
        Add User
      </button>
    </div>
    <div class="card-body">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>S.No</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Image</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($users as $user)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td>{{ $user->phone }}</td>
            <td><img src="{{ asset('uploads/' . $user->upload) }}" width="50"></td>
            <td>
              <a href="#" data-bs-toggle="modal" data-bs-target="#myModal2" data-id="{{ $user->id }}" class="btn btn-primary edit-user">Edit</a>
              <a href="#" data-id="{{ $user->id }}" class="btn btn-danger delete-record">Delete</a>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="myModal">
  <div class="modal-dialog">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title">Add User</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <!-- Modal Body -->
      <div class="modal-body">
        <form id="addUserForm" enctype="multipart/form-data">
          @csrf
          <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" name="name" id="name" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" name="email" id="email" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="phone" class="form-label">Phone</label>
            <input type="text" name="phone" id="phone" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="upload" class="form-label">Image</label>
            <input type="file" name="upload" id="upload" class="form-control" required>
          </div>
          <button type="submit" class="btn btn-primary">Submit</button>
        </form>
      </div>

      <!-- Modal Footer -->
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="myModal2">
  <div class="modal-dialog">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title">Edit</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <!-- Modal Body -->
      <div class="modal-body">
        <div id="edituser" class="row">
          <!-- Dynamic content will be loaded here -->
        </div>
      </div>

      <!-- Modal Footer -->
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Set up CSRF token for AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Handle delete record
    $('.delete-record').click(function() {
        var userId = $(this).data('id');

        Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/users/' + userId,
                    type: 'DELETE',
                    success: function(response) {
                        Swal.fire({
                            title: "Deleted!",
                            text: "Your file has been deleted.",
                            icon: "success"
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: "Error!",
                            text: "There was an error deleting the record.",
                            icon: "error"
                        });
                    }
                });
            }
        });
    });

    // Handle add user form submission
    $('#addUserForm').submit(function(e) {
        e.preventDefault();
        var formData = new FormData(this);

        $.ajax({
            type: 'POST',
            url: '/users',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                alert(response.success);
                $('#myModal').modal('hide');
                location.reload(); // Optionally reload to see the updated data
            },
            error: function(xhr) {
                alert('Error: ' + xhr.responseJSON.error);
            }
        });
    });

    // Handle edit user button click
    $('.edit-user').click(function() {
        var userId = $(this).data('id');
        $.ajax({
            url: '/users/' + userId,
            type: 'GET',
            success: function(response) {
                $('#edituser').html(response.html);
            }
        });
    });

    // Handle edit user form submission
    $(document).on('submit', '#editUserForm', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        var userId = $('#editUserId').val();

        $.ajax({
            url: '/users/' + userId,
            type: 'POST', // Use PUT for update
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                alert('User updated successfully');
                $('#myModal2').modal('hide'); // Close the modal
                location.reload(); // Reload the page to see the updated data
            },
            error: function(xhr) {
                alert('Error updating user');
            }
        });
    });
});
</script>
</body>
</html>
