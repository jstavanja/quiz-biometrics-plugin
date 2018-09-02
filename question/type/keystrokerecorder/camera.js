


// adapted for our needs, reference for the implementation (https://jsfiddle.net/dannymarkov/cuumwch5/)

/******************** DOM SELECTORS ********************/

const video = document.querySelector('#camera-stream')
const image = document.querySelector('#snap')
const controls = document.querySelector('.controls')
const btnTakePhoto = document.querySelector('#take-photo')
const btnDeletePhoto = document.querySelector('#delete-photo')
const btnSendPhoto = document.querySelector('#send-photo')
const btnSendPhotoComparisonTest = document.querySelector('#send-photo-compare')

/******************** NAVIGATIOR API FETCHING ********************/

navigator.getMedia = navigator.mediaDevices.getUserMedia || navigator.mediaDevices.webkitGetUserMedia || navigator.mediaDevices.mozGetUserMedia || navigator.mediaDevices.msGetUserMedia

/******************** OTHER GLOBAL VARIABLES ********************/

let track, snap

/******************** EVENT LISTENERS ********************/

btnTakePhoto.addEventListener("click", (e) => {
  e.preventDefault()

  snap = takeSnapshot()

  image.setAttribute('src', snap)
  image.classList.add("visible")

  btnDeletePhoto.classList.remove("disabled")
  if(btnSendPhoto){
    btnSendPhoto.classList.remove("disabled")
  } else {
    btnSendPhotoComparisonTest.classList.remove("disabled")
  }
})

if(btnSendPhoto){ // gets set in the registration form
  btnSendPhoto.addEventListener('click', function(e) {
    e.preventDefault()
    fetch(snap)
    .then(() => {
      const formData = new FormData()
      formData.append('user_id', window.currentUser)
      formData.append('face_image', base64ToBlob(snap))
      formData.append('quiz_id', quizID)
      formData.append('timing_matrix', JSON.stringify(convertToCSV(allData)))
      axios.post('http://localhost:8000/student/register', formData)
        .then(() => {
          $('#image-upload-loader').hide()
          $('#face_finish_text').show()
          $('#image-upload-inputs').hide()
          stopVideo()
        }).catch((err) => {
          console.log(err.name + ": " + err.message);
        })
    })
  })
}

if(btnSendPhotoComparisonTest){ // gets set in the test form
  btnSendPhotoComparisonTest.addEventListener('click', (e) => {
    e.preventDefault()
    fetch(snap)
    .then(() => {
      const formData = new FormData()
      formData.append('user_id', window.currentUser)
      formData.append('current_image', base64ToBlob(snap))
      formData.append('quiz_id', quizID)
      axios.post('http://localhost:8000/face/distance', formData)
        .then(() => {
          $('#image-upload-loader').hide()
          $('#face_finish_text').show()
          $('#image-upload-inputs').hide()
          stopVideo()
        })
    })
  })
}

btnDeletePhoto.addEventListener("click", (e) => {
  e.preventDefault()
  image.setAttribute('src', "")
  image.classList.remove("visible")
  btnDeletePhoto.classList.add("disabled")
  if(btnSendPhoto){
    btnSendPhoto.classList.add("disabled")
  } else {
    btnSendPhotoComparisonTest.classList.add("disabled")
  }
  video.play()
})

/******************** OTHER FUNCTIONALITY HELPERS ********************/

const takeSnapshot = () => {
  let hidden_canvas = document.querySelector('canvas')
  let context = hidden_canvas.getContext('2d')

  let width = video.videoWidth
  let height = video.videoHeight

  if (width && height) {

    hidden_canvas.width = width
    hidden_canvas.height = height

    context.drawImage(video, 0, 0, width, height)

    return hidden_canvas.toDataURL('image/png')
  }
}

/******************** UI HELPER FUNCTIONS ********************/

const showVideo = () => {
    hideUI()
    video.classList.add("visible")
    controls.classList.add("visible")
}

const startVideo = () => {
    if (!navigator.mediaDevices.getUserMedia) {
        alert("Your browser does not have support for webcam recording. Please contact your professor/assistant")
    } else {
      navigator.mediaDevices.getUserMedia({video: true })
        .then((str) => {
          track = str.getTracks()[0]
          try {
            video.srcObject = mediaSource
          } catch (error) {
            video.src = URL.createObjectURL(str)
          }
          video.play()
          video.onplay = () => {
              showVideo()
          }
        })
      .catch((err) => {
        console.log(err.name + ": " + err.message)
      })
    }
}

const stopVideo = () => {
    video.pause()
    track.stop()
}

const hideUI = () => {
  controls.classList.remove("visible")
  video.classList.remove("visible")
}
